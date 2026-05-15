<?php

namespace App\Actions\Data\Submission\Reimbursement;

use App\Models\Reimbursement;
use Illuminate\Support\Facades\Auth;

class GetAllReimbursement
{
    /**
     * Get all reimbursement.
     *
     * @return array
     */
    public function execute()
    {
        $isSuperAdmin = Auth::user()->hasRole('Superadmin');

        $data = Reimbursement::with(['employee', 'employee.branch', 'approvedBy'])
            ->latest()
            ->when(!$isSuperAdmin && Auth::user()->employee->branch_id != 2, function ($query) {
                $query->whereHas('employee', function ($q) {
                    $q->where('branch_id', Auth::user()->employee->branch_id);
                });
            })->get();

        return $data;
    }
}
