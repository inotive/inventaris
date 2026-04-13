<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class FinancialInformationSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('financial_information')->insert([
            [
                'user_id' => 1,
                'basic_salary' => 5000000,
                'performance_allowance' => 1000000,
                'meal_allowance' => 500000,
                'bpjs_health_allowance' => 200000,
                'bpjs_employment_allowance' => 150000,
                'operational_allowance' => 300000,
                'overtime_allowance' => 250000,
                'housing_allowance' => 1000000,
                'holiday_allowance' => 500000,
                'other_allowance' => 100000,
                'total_income' => 8800000,

                'loan_deduction' => 500000,
                'catering_deduction' => 200000,
                'bpjs_health_deduction' => 200000,
                'bpjs_employment_deduction' => 150000,
                'pph21_deduction' => 400000,
                'cash_advance_deduction' => 100000,
                'operational_deduction' => 50000,
                'other_deduction' => 50000,
                'total_deduction' => 1650000,

                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'user_id' => 2,
                'basic_salary' => 4000000,
                'performance_allowance' => 800000,
                'meal_allowance' => 400000,
                'bpjs_health_allowance' => 180000,
                'bpjs_employment_allowance' => 140000,
                'operational_allowance' => 200000,
                'overtime_allowance' => 100000,
                'housing_allowance' => 800000,
                'holiday_allowance' => 400000,
                'other_allowance' => 50000,
                'total_income' => 7070000,

                'loan_deduction' => 300000,
                'catering_deduction' => 150000,
                'bpjs_health_deduction' => 180000,
                'bpjs_employment_deduction' => 140000,
                'pph21_deduction' => 300000,
                'cash_advance_deduction' => 50000,
                'operational_deduction' => 40000,
                'other_deduction' => 60000,
                'total_deduction' => 1170000,

                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
