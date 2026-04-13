<?php

namespace App\Actions\Data\Dashboard;

use App\Models\Item;
use App\Models\ItemCategory;
use Illuminate\Support\Facades\DB;

class GetItemProportions
{
    public function execute()
    {
        // Ambil nama item dan jumlah stok item
        $items =ItemCategory::select('id', 'name')->withCount('items')->get()->map(function ($item) {
            return [
                'name' => $item->name,
                'stock' => $item->items_count
            ];
        });




        if ($items->isEmpty()) {
            return [
                'labels' => [],
                'data' => [],
                'colors' => []
            ];
        }

        $labels = $items->pluck('name')->toArray();
        $data = $items->pluck('stock')->toArray();

        // Generate warna untuk setiap item
        $colors = ['#1B84FF', '#F59E0B', '#10B981', '#8B5CF6', '#EF4444', '#06B6D4', '#84CC16', '#F97316'];
        // Jika item lebih banyak dari warna, ulangi warna
        $itemColors = [];
        $colorCount = count($colors);
        foreach ($labels as $i => $label) {
            $itemColors[] = $colors[$i % $colorCount];
        }

        return [
            'labels' => $labels,
            'data' => $data,
            'colors' => $itemColors
        ];
    }
}
