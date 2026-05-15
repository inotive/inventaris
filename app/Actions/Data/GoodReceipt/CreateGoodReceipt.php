<?php

namespace App\Actions\Data\GoodReceipt;

use App\Actions\Data\GoodReceipt\CreateGoodReceiptLogActivities;
use App\Actions\Data\Item\UpdateStockItem;
use App\Models\GoodReceipt;
use App\Models\GoodTransfer;
use App\Models\Item;
use App\Models\PurchaseOrder;
use Illuminate\Support\Facades\Auth;

class CreateGoodReceipt
{
    public function execute($data)
    {
        $goodReceipt = GoodReceipt::create($data);

        $sourceName = $data['source'];
        if($data['source'] === 'Pembelian') {
            $data['source'] = PurchaseOrder::class;
            $sourceName = "Pembelian";
        } elseif($data['source'] === 'Pemindahan') {
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
            if($data['source'] === PurchaseOrder::class) {
                $name = "Pembelian";
            } elseif($data['source'] === GoodTransfer::class) {
                $name = "Pemindahan";
            }

            $stockInData = [
                'type' => 'in',
                'amount' => $item['quantity_received'],
                'item_id' => $item['item_id'],
                'source_type' => $data['source'],
                'source_id' => $data['order_id'] ?? $data['transfer_id'],
                'tanggal' => now()->toDateString(),
                'note' => 'Penerimaan barang berhasil diterima oleh ' . Auth::user()->employee?->name . ' dari ' . $name,
            ];

            $item = Item::find($item['item_id']);
            app(UpdateStockItem::class)->execute($stockInData, $item);
        }

        return $goodReceipt;
    }
}
