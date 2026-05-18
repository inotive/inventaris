<?php

namespace App\Actions\Data\GoodReceipt;

use App\Actions\Data\GoodReceipt\CreateGoodReceiptLogActivities;
use App\Actions\Data\Item\UpdateStockItem;
use App\Models\GoodReceipt;
use App\Models\GoodTransfer;
use App\Models\Item;
use App\Models\ItemDisposal;
use App\Models\PurchaseOrder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class CreateGoodReceipt
{
    public function execute($data)
    {
        // Handle multiple file attachments upload
        $attachmentPaths = [];
        $attachmentNames = [];

        if (!empty($data['attachments']) && is_array($data['attachments'])) {
            foreach ($data['attachments'] as $file) {
                if (is_object($file) && method_exists($file, 'store')) {
                    $attachmentNames[] = $file->getClientOriginalName();
                    $attachmentPaths[] = $file->store('good-receipts/attachments', 'public');
                }
            }
        }

        // Fallback for single attachment
        if (!empty($data['attachment']) && is_object($data['attachment']) && method_exists($data['attachment'], 'store')) {
            $attachmentNames[] = $data['attachment']->getClientOriginalName();
            $attachmentPaths[] = $data['attachment']->store('good-receipts/attachments', 'public');
        }

        $goodReceipt = GoodReceipt::create([
            'employee_id'     => $data['employee_id'],
            'order_id'        => $data['order_id'] ?? null,
            'transfer_id'     => $data['transfer_id'] ?? null,
            'source'          => $data['source'],
            'note'            => $data['note'] ?? null,
            'received_at'     => $data['received_at'] ?? now(),
            'attachment_path' => !empty($attachmentPaths) ? $attachmentPaths : null,
            'attachment_name' => !empty($attachmentNames) ? $attachmentNames : null,
        ]);

        $sourceName = $data['source'];
        if ($data['source'] === 'Pembelian') {
            $data['source'] = PurchaseOrder::class;
            $sourceName = "Pembelian";
        } elseif ($data['source'] === 'Pemindahan') {
            $data['source'] = GoodTransfer::class;
            $sourceName = "Pemindahan";
        }

        $goodReceipt->items()->createMany($data['items']);

        // Log activity
        $sourceReference = null;
        if (isset($data['order_id'])) {
            $order = PurchaseOrder::find($data['order_id']);
            $sourceReference = $order ? $order->order_no : null;
        } elseif (isset($data['transfer_id'])) {
            $transfer = GoodTransfer::find($data['transfer_id']);
            $sourceReference = $transfer ? $transfer->transfer_no : null;
        }

        app(CreateGoodReceiptLogActivities::class)->execute(
            $goodReceipt->id,
            'Penerimaan Barang Dibuat',
            'Penerimaan barang berhasil dibuat oleh ' . Auth::user()->employee?->name . ' dari ' . $sourceName . ($sourceReference ? ' (' . $sourceReference . ')' : '')
        );

        foreach ($data['items'] as $item) {
            if ($data['source'] === PurchaseOrder::class) {
                $name = "Pembelian";
            } elseif ($data['source'] === GoodTransfer::class) {
                $name = "Pemindahan";
            }

            $stockInData = [
                'type'        => 'in',
                'amount'      => $item['quantity_received'],
                'item_id'     => $item['item_id'],
                'source_type' => $data['source'],
                'source_id'   => $data['order_id'] ?? $data['transfer_id'],
                'tanggal'     => now()->toDateString(),
                'note'        => 'Penerimaan barang berhasil diterima oleh ' . Auth::user()->employee?->name . ' dari ' . $name,
            ];

            $originalItem = Item::find($item['item_id']);
            $receiverBranchId = Auth::user()->employee?->branch_id;

            // Find matching item in receiver's branch
            $targetItem = $originalItem;
            if ($data['source'] !== GoodTransfer::class && $receiverBranchId && $receiverBranchId != $originalItem->branch_id) {
                $itemInReceiverBranch = Item::where('name', $originalItem->name)
                    ->where('branch_id', $receiverBranchId)
                    ->first();
                if ($itemInReceiverBranch) {
                    $targetItem = $itemInReceiverBranch;
                    $stockInData['item_id'] = $targetItem->id;
                }
            }

            app(UpdateStockItem::class)->execute($stockInData, $targetItem);

            // Handle shortage: rusak atau hilang (supports multiple shortages per item)
            if (!empty($item['shortages']) && is_array($item['shortages'])) {
                foreach ($item['shortages'] as $sh) {
                    $shQty = isset($sh['quantity']) ? (int) $sh['quantity'] : 0;
                    if ($shQty > 0 && !empty($sh['reason'])) {
                        ItemDisposal::create([
                            'disposal_no'   => ItemDisposal::generateDisposalNo(),
                            'item_id'       => $item['item_id'],
                            'quantity'      => $shQty,
                            'reason'        => $sh['reason'],
                            'description'   => $sh['description'] ?? null,
                            'source_type'   => GoodReceipt::class,
                            'source_id'     => $goodReceipt->id,
                            'reported_by'   => Auth::user()->employee?->id,
                            'disposal_date' => now()->toDateString(),
                        ]);

                        // Log disposal activity
                        app(CreateGoodReceiptLogActivities::class)->execute(
                            $goodReceipt->id,
                            'Barang ' . ($sh['reason'] === 'rusak' ? 'Rusak/Cacat' : 'Hilang') . ' Dicatat',
                            'Sebanyak ' . $shQty . ' unit barang ' . ($originalItem->name ?? '') .
                                ' dicatat sebagai ' . ($sh['reason'] === 'rusak' ? 'rusak/cacat' : 'hilang') .
                                ($sh['description'] ? '. Keterangan: ' . $sh['description'] : '')
                        );
                    }
                }
            } else {
                // Fallback for single shortage fields
                $shortageQty = isset($item['shortage_quantity']) ? (int) $item['shortage_quantity'] : 0;
                if ($shortageQty > 0 && !empty($item['shortage_reason'])) {
                    ItemDisposal::create([
                        'disposal_no'  => ItemDisposal::generateDisposalNo(),
                        'item_id'      => $item['item_id'],
                        'quantity'     => $shortageQty,
                        'reason'       => $item['shortage_reason'],
                        'description'  => $item['shortage_description'] ?? null,
                        'source_type'  => GoodReceipt::class,
                        'source_id'    => $goodReceipt->id,
                        'reported_by'  => Auth::user()->employee?->id,
                        'disposal_date' => now()->toDateString(),
                    ]);

                    // Log disposal activity
                    app(CreateGoodReceiptLogActivities::class)->execute(
                        $goodReceipt->id,
                        'Barang ' . ($item['shortage_reason'] === 'rusak' ? 'Rusak/Cacat' : 'Hilang') . ' Dicatat',
                        'Sebanyak ' . $shortageQty . ' unit barang ' . ($originalItem->name ?? '') .
                            ' dicatat sebagai ' . ($item['shortage_reason'] === 'rusak' ? 'rusak/cacat' : 'hilang') .
                            ($item['shortage_description'] ? '. Keterangan: ' . $item['shortage_description'] : '')
                    );
                }
            }
        }

        return $goodReceipt;
    }
}
