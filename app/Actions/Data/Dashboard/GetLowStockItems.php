<?php

namespace App\Actions\Data\Dashboard;

use App\Models\Item;
use App\Models\ItemStock;
use Illuminate\Support\Facades\DB;

class GetLowStockItems
{
    public function execute($limit = 50)
    {
        // Get latest stock for each item using subquery
        $latestStockIds = ItemStock::select('item_id')
            ->selectRaw('MAX(id) as max_id')
            ->groupBy('item_id')
            ->get()
            ->pluck('max_id')
            ->filter()
            ->values();

        // Get latest stocks keyed by item_id
        $latestStocks = collect();
        if ($latestStockIds->isNotEmpty()) {
            $latestStocks = ItemStock::whereIn('id', $latestStockIds)
                ->get()
                ->keyBy('item_id');
        }

        // Get all items with their latest stock
        $items = Item::select([
                'id',
                'name',
                'code',
                'min_stock',
            ])
            ->orderBy('name', 'asc')
            ->limit($limit)
            ->get()
            ->map(function ($item) use ($latestStocks) {
                // Get latest stock for this item
                $latestStock = $latestStocks->get($item->id);
                $actualStock = $latestStock ? (int) ($latestStock->last_stock ?? 0) : 0;
                $minimumStock = (int) ($item->min_stock ?? 0);
                $difference = $actualStock - $minimumStock;

                return [
                    'id' => $item->id,
                    'name' => $item->name ?? '-',
                    'code' => $item->code ?? '-',
                    'actual_stock' => $actualStock,
                    'minimum_stock' => $minimumStock,
                    'difference' => $difference,
                    'price' => null, // Price tidak ada di tabel items
                ];
            })
            ->values()
            ->toArray();

        return $items;
    }
}
