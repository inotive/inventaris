<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class UpdateBalanceEmployee extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:update-balance-employee';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update employee leave balances and receivable balances yearly';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $currentYear = now()->year;

        // Get all active employees (not resigned or resigned date is null or in the future)
        $employees = \App\Models\Employee::where(function ($query) {
            $query->whereNull('resign_date')
                ->orWhere('resign_date', '>', now());
        })->get();

        $this->info("Updating balances for {$employees->count()} employees for year {$currentYear}");

        // Update Leave Balances
        $leaveTypes = \App\Models\LeaveType::all();
        $leaveBalanceCount = 0;

        foreach ($employees as $employee) {
            foreach ($leaveTypes as $leaveType) {
                // Create or update leave balance for each leave type
                \App\Models\EmployeeLeaveBalance::updateOrCreate(
                    [
                        'employee_id' => $employee->id,
                        'leave_type_id' => $leaveType->id,
                        'year' => $currentYear,
                    ],
                    [
                        'total_quota' => $leaveType->leave_quota_per_year ?? 0,
                        'used_quota' => 0,
                        'remaining_quota' => $leaveType->leave_quota_per_year ?? 0,
                    ]
                );
                $leaveBalanceCount++;
            }
        }

        $this->info("✓ Updated {$leaveBalanceCount} leave balance records");

        // Update Receivable Balances
        $receivableBalanceCount = 0;
        $currentMonth = now()->month;

        foreach ($employees as $employee) {
            // Create receivable balance for January (month 1) of the new year
            \App\Models\EmployeeReceivableBalance::updateOrCreate(
                [
                    'employee_id' => $employee->id,
                    'period_year' => $currentYear,
                    'period_month' => 1, // January
                ],
                [
                    'limit_amount' => $employee->loan_quota ?? 0,
                    'used_amount' => 0,
                    'remaining_amount' => $employee->loan_quota ?? 0,
                    'policy' => 'yearly',
                ]
            );
            $receivableBalanceCount++;
        }

        $this->info("✓ Updated {$receivableBalanceCount} receivable balance records");
        $this->info("Balance update completed successfully!");

        return 0;
    }
}
