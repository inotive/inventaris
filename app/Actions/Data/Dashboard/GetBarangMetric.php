<?php

namespace App\Actions\Data\Dashboard;

use App\Models\Item;
use App\Models\ItemStock;
use App\Models\ItemMovement;
use Carbon\Carbon;

class GetBarangMetric
{
    public function execute()
    {
        $currentYear = Carbon::now()->year;

        // Get total stock from latest ItemStock records using subquery
        $totalStock = ItemStock::whereIn('id', function ($query) {
            $query->selectRaw('MAX(id)')
                ->from('item_stocks')
                ->groupBy('item_id');
        })->sum('last_stock') ?? 0;

        // Get low stock items count (items with latest stock < 40)
        $lowStockItems = Item::whereHas('stock', function ($query) {
            $query->where('last_stock', '<', 40);
        })->count();

        // Get out of stock items count (items with latest stock = 0 or no stock)
        $outOfStockItems = Item::where(function ($query) {
            $query->whereDoesntHave('stock')
                ->orWhereHas('stock', function ($q) {
                    $q->where('last_stock', '<=', 0);
                });
        })->count();

        return [
            'total_stock' => (int) $totalStock,
            'low_stock_items' => (int) $lowStockItems,
            'out_of_stock' => (int) $outOfStockItems,
            'total_transactions' => (int) ItemStock::count(),
        ];
    }
}
