<?php

namespace App\Actions\Data\Dashboard;

use App\Models\GoodIssueItem;
use Illuminate\Support\Facades\DB;

class GetMostFrequentlyUsedItems
{
    public function execute($limit = 6)
    {
        return GoodIssueItem::select([
                'items.name',
                DB::raw('SUM(good_issue_items.quantity_issued) as total_usage')
            ])
            ->join('items', 'good_issue_items.item_id', '=', 'items.id')
            ->groupBy('items.id', 'items.name')
            ->orderBy('total_usage', 'desc')
            ->limit($limit)
            ->get()
            ->map(function ($item) {
                return [
                    'name' => $item->name,
                    'usage' => (int) $item->total_usage
                ];
            });
    }
}
