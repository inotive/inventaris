<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Employee;
use App\Models\EmployeeReceivableBalance;

class EmployeeReceivableBalanceSeeder extends Seeder
{
    public function run(): void
    {
        $defaultLimit = 2000000; // Rp 2.000.000
        $year = now()->year;
        $month = now()->month;

        $employees = Employee::query()->select('id')->get();
        foreach ($employees as $emp) {
            EmployeeReceivableBalance::updateOrCreate(
                [
                    'employee_id' => $emp->id,
                    'period_year' => $year,
                    'period_month' => $month,
                ],
                [
                    'limit_amount' => $defaultLimit,
                    'used_amount' => 0,
                    'remaining_amount' => $defaultLimit,
                    'policy' => 'monthly',
                ],
            );
        }
    }
}
