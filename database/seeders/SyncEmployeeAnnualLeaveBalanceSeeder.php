<?php

namespace Database\Seeders;

use App\Enums\EmployeeLeaveRequestStatusEnum;
use App\Enums\LeaveTypeCategoryEnum;
use App\Models\Employee;
use App\Models\EmployeeLeaveBalance;
use App\Models\EmployeeLeaveRequest;
use App\Models\LeaveType;
use Illuminate\Database\Seeder;

class SyncEmployeeAnnualLeaveBalanceSeeder extends Seeder
{
    /**
     * Seeder khusus sinkronisasi saldo cuti tahunan tahun berjalan.
     */
    public function run(): void
    {
        $year = now()->year;

        $annualLeaveTypeId = LeaveType::query()
            ->where('category', LeaveTypeCategoryEnum::ANNUAL_LEAVE->value)
            ->value('id');

        if (!$annualLeaveTypeId) {
            $annualLeaveTypeId = LeaveType::query()->where('id', 1)->value('id');
        }

        if (!$annualLeaveTypeId) {
            $this->command?->warn('SyncEmployeeAnnualLeaveBalanceSeeder: leave type cuti tahunan tidak ditemukan.');
            return;
        }

        Employee::query()
            ->select(['id', 'leave_quota_per_year'])
            ->chunkById(100, function ($employees) use ($year, $annualLeaveTypeId) {
                foreach ($employees as $employee) {
                    $existsThisYear = EmployeeLeaveBalance::query()
                        ->where('employee_id', $employee->id)
                        ->where('leave_type_id', $annualLeaveTypeId)
                        ->where('year', $year)
                        ->exists();

                    if ($existsThisYear) {
                        continue;
                    }

                    $usedQuota = (int) EmployeeLeaveRequest::query()
                        ->where('employee_id', $employee->id)
                        ->where('leave_type_id', $annualLeaveTypeId)
                        ->where('status', EmployeeLeaveRequestStatusEnum::APPROVED->value)
                        ->where(function ($query) use ($year) {
                            $query->whereYear('start_date', $year)
                                ->orWhereYear('end_date', $year)
                                ->orWhere(function ($subQuery) use ($year) {
                                    $subQuery->whereYear('start_date', '<', $year)
                                        ->whereYear('end_date', '>', $year);
                                });
                        })
                        ->sum('total_days');

                    $totalQuota = (int) ($employee->leave_quota_per_year ?? 0);
                    $remainingQuota = max(0, $totalQuota - $usedQuota);

                    EmployeeLeaveBalance::query()->create([
                        'employee_id' => $employee->id,
                        'leave_type_id' => $annualLeaveTypeId,
                        'year' => $year,
                        'total_quota' => $totalQuota,
                        'used_quota' => $usedQuota,
                        'remaining_quota' => $remainingQuota,
                    ]);
                }
            });

        $this->command?->info('SyncEmployeeAnnualLeaveBalanceSeeder selesai untuk tahun ' . $year . '.');
    }
}

