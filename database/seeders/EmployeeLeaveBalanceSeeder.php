<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\EmployeeLeaveBalance;
use App\Models\Employee;
use App\Models\LeaveType;

class EmployeeLeaveBalanceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $employees = Employee::all();
        $leaveTypes = LeaveType::all();
        $year = date('Y');

        foreach ($employees as $employee) {
            foreach ($leaveTypes as $leaveType) {
                $totalQuota = rand(5, 20); // total quota per leave type
                $usedQuota = rand(0, $totalQuota); // random used quota
                $remainingQuota = $totalQuota - $usedQuota;

                EmployeeLeaveBalance::create([
                    'employee_id' => $employee->id,
                    'leave_type_id' => $leaveType->id,
                    'year' => $year,
                    'total_quota' => $totalQuota,
                    'used_quota' => $usedQuota,
                    'remaining_quota' => $remainingQuota,
                ]);
            }
        }
    }
}
