<?php

namespace App\Actions\Data\Submission\Sick;

use App\Enums\SubmissionStatusEnum;
use App\Models\Attendance;
use App\Models\EmployeeLeaveBalance;
use App\Models\EmployeeLeaveRequest;
use App\Models\Submission;
use App\Services\NotificationService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class UpdateSubmissionSick
{
    /**
     * Update submission based on type and status.
     *
     * @param array $data
     * @param EmployeeLeaveRequest $submission
     * @return array
     */
    public function execute($data, $submission)
    {
        DB::beginTransaction();
        // Send notification to staff about status change
        try {

            if ($data['status'] == 'approved') {
                $usedQuota = $submission->total_days;

                if ($submission->BalanceLeave) {
                    $remainingQuota = $submission->BalanceLeave->remaining_quota - $usedQuota;
                } else {
                    $remainingQuota = $submission->employee->leave_quota_per_year - $usedQuota;
                }


                $sict = [
                    'sick_leave' => 'SAKIT',
                    'annual_leave' => 'CUTI',
                    'special_leave' => 'IZIN',
                ];
                $status = $sict[$submission->leaveType->category->value];

                $year = $submission->start_date->format('Y');

                if ($submission->leaveType->category->value != 'sick_leave') {
                    $employeeLeaveBalance = EmployeeLeaveBalance::where('employee_id', $submission->employee_id)
                        ->where('leave_type_id', $submission->leave_type_id)
                        ->where('year', $year)
                        ->first();

                    if ($employeeLeaveBalance && $employeeLeaveBalance->remaining_quota < $usedQuota) {
                        DB::rollBack();
                        return [
                            'success' => false,
                            'message' => 'Sisa saldo cuti tidak mencukupi.',
                        ];
                    }

                    if ($employeeLeaveBalance) {
                        $usedQuotaAkumulasi = $employeeLeaveBalance->used_quota + $usedQuota;
                        $remainingQuota = $employeeLeaveBalance->remaining_quota - $usedQuota;

                        $employeeLeaveBalance->update([
                            'used_quota' => $usedQuotaAkumulasi,
                            'remaining_quota' => $remainingQuota,
                        ]);
                    } else {
                        EmployeeLeaveBalance::create([
                            'employee_id' => $submission->employee_id,
                            'leave_type_id' => $submission->leave_type_id,
                            'year' => $year,
                            'total_quota' => $submission->employee->leave_quota_per_year,
                            'used_quota' => $usedQuota,
                            'remaining_quota' => $remainingQuota,
                        ]);
                    }
                }



                $dateWork = $submission->employee->attendanceShiftWorks()->where('work_date', '>=', $submission->start_date)->limit($usedQuota)->pluck('work_date');

                foreach ($dateWork as $date) {
                    Attendance::create([
                        'employee_id' => $submission->employee_id,
                        'department_id' => $submission->employee->department_id,
                        'tanggal' => $date,
                        'status' => $status,
                        'note' => $data['admin_notes'] ?? '',
                    ]);
                }
            }

            $submission->update([
                'status' => $data['status'],
                'admin_notes' => $data['admin_notes'],
                'approved_at' => now(),
                'approved_by' => Auth::user()->employee->id,
            ]);

            $notificationService = app(NotificationService::class);
            $notificationService->notifyStaffOnFeedback(
                'sick',
                $submission->id,
                $submission->employee->user_id,
                $data['status'],
                ['note' => $data['admin_notes'] ?? '']
            );

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
            // Silent fail – notification should not block API
        }
    }
}
