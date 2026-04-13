<?php

namespace App\Actions\Data\Dashboard;

use App\Models\Item;
use App\Models\ItemStock;
use App\Models\ItemMovement;
use Illuminate\Support\Facades\DB;

class GetBarangChartData
{
    public function execute($year)
    {
        $result = [];
        // Get monthly data for items from item stocks using Eloquent
        $monthlyData = ItemStock::select(
                DB::raw('MONTH(created_at) as month'),
                DB::raw('SUM(CASE WHEN type = "In" OR type = "in" THEN amount ELSE 0 END) as masuk'),
                DB::raw('SUM(CASE WHEN type = "Out" OR type = "out" THEN amount ELSE 0 END) as keluar')
            )
            ->whereYear('created_at', $year)
            ->groupBy(DB::raw('MONTH(created_at)'))
            ->orderBy('month')
            ->get();

        // Initialize data for all months
        $chartData = [
            'categories' => ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'],
            'series' => [
                'masuk' => array_fill(0, 12, 0),
                'keluar' => array_fill(0, 12, 0)
            ]
        ];

        // Fill in actual data
        foreach ($monthlyData as $row) {
            $monthIndex = $row->month - 1; // Convert to 0-based index
            $chartData['series']['masuk'][$monthIndex] = (int) $row->masuk;
            $chartData['series']['keluar'][$monthIndex] = (int) $row->keluar;
        }

        $result['chartData'] = $chartData;

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

        $result['metric'] = [
            'total_stock' => (int) $totalStock,
            'low_stock_items' => (int) $lowStockItems,
            'out_of_stock' => (int) $outOfStockItems,
            'total_transactions' => (int) ItemMovement::count(),
        ];

        return $result;
    }
}
