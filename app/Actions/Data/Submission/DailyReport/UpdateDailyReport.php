<?php

namespace App\Actions\Data\Submission\DailyReport;

use App\Models\DailyReport;
use App\Services\NotificationService;
use Illuminate\Support\Facades\Auth;

class UpdateDailyReport
{
    public function execute($data, DailyReport $dailyReport)
    {
        $updateData = [];

        // Update status if provided
        if (isset($data['status'])) {
            $updateData['status'] = $data['status'];

            // If status is approved or rejected, set approver and approval date
            if (in_array($data['status'], ['approved', 'rejected'])) {
                $updateData['approved_by'] = Auth::user()->employee->id ?? null;
                $updateData['approved_at'] = now();
            }
        }

        // Update other fields if provided
        if (isset($data['employee_id'])) {
            $updateData['employee_id'] = $data['employee_id'];
        }
        if (isset($data['start_date'])) {
            $updateData['start_date'] = $data['start_date'];
        }
        if (isset($data['end_date'])) {
            $updateData['end_date'] = $data['end_date'];
        }
        if (isset($data['name'])) {
            $updateData['name'] = $data['name'];
        }
        if (isset($data['salary'])) {
            $updateData['salary'] = $data['salary'];
        }
        if (isset($data['reason'])) {
            $updateData['reason'] = $data['reason'];
        }
        if (isset($data['notes'])) {
            $updateData['notes'] = $data['notes'];
        }

        $dailyReport->update($updateData);

        // Send notification to staff about status change
        if (isset($data['status']) && in_array($data['status'], ['approved', 'rejected'])) {
            try {
                $notificationService = app(NotificationService::class);
                $notificationService->notifyStaffOnFeedback(
                    'daily_report',
                    $dailyReport->id,
                    $dailyReport->employee->user_id,
                    $data['status'],
                    ['note' => $data['notes'] ?? '']
                );
            } catch (\Throwable $e) {
                // Silent fail – notification should not block API
            }
        }

        return $dailyReport;
    }
}
