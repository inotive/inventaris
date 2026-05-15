<?php

namespace App\Actions\Data\Dashboard;

use App\Models\GoodReceipt;
use Carbon\Carbon;

class GetGoodReceiptData
{
    public function execute()
    {
        $currentYear = Carbon::now()->year;
        $lastYear = $currentYear - 1;

        return [
            'current' => GoodReceipt::whereDate('received_at', Carbon::today())->count(),
            'total_2026' => GoodReceipt::whereYear('received_at', $currentYear)->count(),
            'completed' => GoodReceipt::whereYear('received_at', $currentYear)->count(),
            'total_2024' => GoodReceipt::whereYear('received_at', $lastYear)->count(),
        ];
    }
}
