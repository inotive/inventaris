<?php

namespace App\Actions\Data\Item;

use App\Models\ItemStock;
use Illuminate\Support\Facades\DB;

class RecalculateItemStocks
{
    public function execute(int $itemId): void
    {
        DB::transaction(function () use ($itemId) {
            $txs = ItemStock::where('item_id', $itemId)
                ->orderBy('id')
                ->lockForUpdate()
                ->get();

            $running = 0.0;

            foreach ($txs as $tx) {
                $tx->initial_stock = $running;

                $type = strtolower((string) $tx->type);
                $delta = in_array($type, ['in','transfer_in'], true)
                    ? (float) $tx->amount
                    : -(float) $tx->amount;

                $running += $delta;
                $tx->last_stock = $running;
                $tx->save();
            }
        });
    }
}
