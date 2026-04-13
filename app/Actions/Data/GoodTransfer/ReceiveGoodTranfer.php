<?php

namespace App\Actions\Data\GoodTransfer;

use App\Actions\Data\GoodReceipt\CreateGoodReceipt;
use App\Actions\Data\Item\UpdateStockItem;
use App\Models\GoodReceipt;
use App\Models\GoodTransfer;
use App\Models\Item;
use Illuminate\Support\Facades\Auth;

class ReceiveGoodTranfer
{
    public function execute($goodTransfer, $request)
    {
        $goodTransfer->update([
            'status' => 'Received',
            'received_by' => Auth::user()->employee?->id,
        ]);

        foreach ($request['quantities'] as $quantity) {
            $goodTransfer->items()->where('id', $quantity['item_id'])->update([
                'quantity_received' => $quantity['quantity_received'],
                'note_received' => $quantity['receive_note'] ?? null,
            ]);
        }

        // Log activity
        app(CreateGoodTransferLogActivities::class)->execute(
            $goodTransfer->id,
            'Barang Diterima',
            'Pemindahan barang berhasil diterima oleh ' . Auth::user()->employee?->name
        );


        //add penerimaan barang
        $data = [
            'employee_id' => Auth::user()->employee?->id,
            'transfer_id' => $goodTransfer->id,
            'source' => 'Pemindahan',
            'note' => 'Pemindahan barang berhasil diterima oleh ' . Auth::user()->employee?->name,
            'received_at' => now(),
        ];

        // Prepare items for good receipt with correct item_id
        $receiptItems = [];
        foreach ($request['quantities'] as $quantity) {
            // Get the good transfer item to find the actual item_id
            $goodTransferItem = $goodTransfer->items()->find($quantity['item_id']);
            $toBranchItem = $goodTransfer->toBranch->items()->where('code', $goodTransferItem->item->code)->first();

            if ($toBranchItem) {
                $receiptItems[] = [
                    'item_id' => $toBranchItem->id,
                    'quantity_transferred' => $quantity['quantity_transferred'],
                    'quantity_received' => $quantity['quantity_received'],
                    'note' => $quantity['receive_note'] ?? null,
                    'source' => GoodTransfer::class,
                    'source_id'=> $goodTransfer->id
                ];
            }
        }
        $data['items'] = $receiptItems;

        app(CreateGoodReceipt::class)->execute($data);
    }
}
