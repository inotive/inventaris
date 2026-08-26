<?php

namespace App\Actions\Data\GoodTransfer;

use App\Actions\Data\GoodReceipt\CreateGoodReceipt;
use App\Actions\Data\GoodReceipt\CreateGoodReceiptLogActivities;
use App\Actions\Data\Item\UpdateStockItem;
use App\Models\GoodReceipt;
use App\Models\GoodTransfer;
use App\Models\Item;
use App\Models\ItemDisposal;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class ReceiveGoodTranfer
{
    public function execute($goodTransfer, $request)
    {
        $goodTransfer->update([
            'status'      => 'Received',
            'received_by' => Auth::user()->employee?->id,
        ]);

        foreach ($request['quantities'] as $quantity) {
            $goodTransfer->items()->where('id', $quantity['item_id'])->update([
                'quantity_received' => $quantity['quantity_received'],
                'note_received'     => $quantity['receive_note'] ?? null,
            ]);
        }

        // Log activity
        app(CreateGoodTransferLogActivities::class)->execute(
            $goodTransfer->id,
            'Barang Diterima',
            'Pemindahan barang berhasil diterima oleh ' . Auth::user()->employee?->name
        );

        // add penerimaan barang
        $data = [
            'employee_id'     => Auth::user()->employee?->id,
            'transfer_id'     => $goodTransfer->id,
            'source'          => 'Pemindahan',
            'note'            => 'Pemindahan barang berhasil diterima oleh ' . Auth::user()->employee?->name,
            'received_at'     => now(),
            'attachment'      => $request['attachment'] ?? null,
            'attachments'     => $request['attachments'] ?? null,
        ];

        // Prepare items for good receipt with correct item_id
        $receiptItems = [];
        foreach ($request['quantities'] as $quantity) {
            // Get the good transfer item to find the actual item_id
            $goodTransferItem = $goodTransfer->items()->find($quantity['item_id']);

            // Lewati baris yang barangnya sudah diarsipkan (soft delete)
            // agar penerimaan tidak crash karena relasi item null.
            if (!$goodTransferItem || !$goodTransferItem->item) {
                Log::warning('ReceiveGoodTransfer: baris transfer dilewati karena barang sudah diarsipkan', [
                    'good_transfer_id' => $goodTransfer->id,
                    'good_transfer_item_id' => $quantity['item_id'] ?? null,
                ]);
                continue;
            }

            // Cari item di cabang tujuan, termasuk yang terarsip (withTrashed),
            // agar kode barang yang sudah di-soft delete tetap bisa diterima.
            $toBranchItem = $goodTransfer->toBranch->items()->withTrashed()->where('code', $goodTransferItem->item->code)->first();

            if (!$toBranchItem) {
                $toBranchItem = $goodTransfer->toBranch->items()->withTrashed()->where('name', $goodTransferItem->item->name)->first();
            }

            if (!$toBranchItem) {
                $toBranchItem = Item::create([
                    'branch_id'   => $goodTransfer->to_branch,
                    'unit_id'     => $goodTransferItem->item->unit_id,
                    'category_id' => $goodTransferItem->item->category_id,
                    'name'        => $goodTransferItem->item->name,
                    'code'        => $goodTransferItem->item->code,
                    'description' => $goodTransferItem->item->description,
                    'min_stock'   => $goodTransferItem->item->min_stock,
                ]);
            }

            if ($toBranchItem) {
                $receiptItems[] = [
                    'item_id'            => $toBranchItem->id,
                    'quantity_transferred' => $quantity['quantity_transferred'],
                    'quantity_received'  => $quantity['quantity_received'],
                    'note'               => $quantity['receive_note'] ?? null,
                    'source'             => GoodTransfer::class,
                    'source_id'          => $goodTransfer->id,
                    'shortages'          => $quantity['shortages'] ?? null,
                    'shortage_quantity'  => $quantity['shortage_quantity'] ?? 0,
                    'shortage_reason'    => $quantity['shortage_reason'] ?? null,
                    'shortage_description' => $quantity['shortage_description'] ?? null,
                ];
            }
        }
        $data['items'] = $receiptItems;

        $goodReceipt = app(CreateGoodReceipt::class)->execute($data);
    }
}
