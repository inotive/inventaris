<?php

namespace App\Http\Controllers\Api\v1;

use App\Helpers\ResponseFormatter;
use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\SalarySlip;
use Illuminate\Support\Facades\Auth;

class FinancialSummaryController extends Controller
{
    public function summary()
    {
        $user = Auth::user();
        $employee = $user?->employee;
        if (!$employee) {
            return ResponseFormatter::error('Employee not found for authenticated user', 400);
        }

        // Base salary from profile (employee)
        $baseSalary = (float) ($employee->salary ?? 0);

        // Latest salary slip (if any) – assume there is a derived total column on another table or
        // fall back to FinancialInformation if needed. For now, we only return the existence of slip.
        $latestSlip = SalarySlip::where('employee_id', $employee->id)
            ->orderByDesc('bulan')
            ->first();

        $payload = [
            'employee_id' => $employee->id,
            'employee_name' => $employee->name,
            'profile_base_salary' => $baseSalary,
            'latest_salary_slip' => $latestSlip ? [
                'id' => $latestSlip->id,
                'bulan' => $latestSlip->bulan,
                'file_url' => $latestSlip->file_url,
            ] : null,
        ];

        return ResponseFormatter::success($payload, 'Financial summary');
    }
}
