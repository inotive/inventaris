<?php

namespace App\Actions\Data\Submission\DailyReport;

use App\Models\DailyReport;
use Illuminate\Support\Facades\Auth;

class GetAllDailyReport
{
    public function execute()
    {
        $isSuperAdmin = Auth::user()->hasRole('Superadmin');
        return DailyReport::with(['employee.branch', 'employee.department', 'approved'])
            ->latest()
            ->when(!$isSuperAdmin && Auth::user()->employee->branch_id != 2, function ($query) {
                $query->whereHas('employee', function ($q) {
                    $q->where('branch_id', Auth::user()->employee->branch_id);
                });
            })
            ->get();
    }
}
