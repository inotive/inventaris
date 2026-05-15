<?php

namespace App\Actions\Data\Submission\General;

use App\Models\GeneralSubmission;
use Illuminate\Support\Facades\Auth;

class GetAllGeneral
{
    /**
     * Get receivable/loan paginate with filters and sorting.
     *
     */
    public function execute()
    {
        $isSuperAdmin = Auth::user()->hasRole('Superadmin');
        return GeneralSubmission::with(['employee', 'employee.branch', 'approver'])
            ->latest()
            ->when(!$isSuperAdmin && Auth::user()->employee->branch_id != 2, function ($query) {
                $query->whereHas('employee', function ($q) {
                    $q->where('branch_id', Auth::user()->employee->branch_id);
                });
            })
            ->get();
    }
}
