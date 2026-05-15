<?php

namespace App\Actions\Data\Submission\DailyReport;

use App\Models\DailyReport;
use App\Services\NotificationService;
use Illuminate\Support\Facades\Auth;

class CreateDailyReport
{
    public function execute($data)
    {
        $dailyReport = DailyReport::create([
            'employee_id' => $data['employee_id'],
            'start_date' => $data['start_date'],
            'end_date' => $data['end_date'],
            'name' => $data['name'],
            'salary' => $data['salary'],
            'reason' => $data['reason'],
            'status' => $data['status'] ?? 'pending',
            'notes' => $data['notes'] ?? null,
        ]);

        // Send notification to admin about new submission
        try {
            $notificationService = app(NotificationService::class);
            $notificationService->notifyAdminOnNewSubmission(
                'daily_report',
                $dailyReport->id,
                $dailyReport->employee->user_id,
                'Pengajuan Laporan Harian Baru'
            );
        } catch (\Throwable $e) {
            // Silent fail – notification should not block creation
        }

        return $dailyReport;
    }
}
