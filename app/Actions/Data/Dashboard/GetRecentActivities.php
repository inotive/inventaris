<?php

namespace App\Actions\Data\Dashboard;

use App\Models\LogActivity;

class GetRecentActivities
{
    public function execute()
    {
        $recentLogs = LogActivity::orderBy('created_at', 'desc')
            ->limit(5)
            ->get()
            ->map(function ($log) {
                return [
                    'description' => $log->description ?? 'Aktivitas sistem',
                    'date' => $log->created_at->format('d F Y - H:i'),
                    'type' => 'log'
                ];
            });

        return $recentLogs;
    }
}
