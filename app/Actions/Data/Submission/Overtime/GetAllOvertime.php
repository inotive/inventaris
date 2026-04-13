<?php

namespace App\Actions\Data\Submission\Overtime;

use App\Models\EmployeeOvertime;
use Illuminate\Support\Facades\Auth;

class GetAllOvertime
{
    /**
     * Get all overtime.
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function execute()
    {
        $isSuperAdmin = Auth::user()->hasRole('Superadmin');
        return EmployeeOvertime::with(['employee', 'employee.branch', 'approved'])
            ->latest()
            ->when(!$isSuperAdmin && Auth::user()->employee->branch_id != 2, function ($query) {
                $query->whereHas('employee', function ($q) {
                    $q->where('branch_id', Auth::user()->employee->branch_id);
                });
            })
            ->get();
    }
}
