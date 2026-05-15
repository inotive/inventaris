<?php

namespace App\Actions\Data\Dashboard;

use App\Models\MaterialRequest;
use Carbon\Carbon;

class GetMaterialRequestData
{
    public function execute()
    {
        $currentYear = Carbon::now()->year;
        $lastYear = $currentYear - 1;

        return [
            'current' => MaterialRequest::where('status', 'on_progress')->orWhere('status', 'partial_approved')->count(),
            'total_2026' => MaterialRequest::whereYear('created_at', $currentYear)->count(),
            'completed' => MaterialRequest::where('status', 'approved')->orWhere('status', 'rejected')
                ->whereYear('created_at', $currentYear)
                ->count(),
            'total_2024' => MaterialRequest::whereYear('created_at', $lastYear)->count(),
        ];
    }
}
