<?php

namespace App\Actions\Data\Submission\Loan;

use App\Models\Receivable;
use Illuminate\Support\Facades\Auth;

class GetAllLoan
{
    /**
     * Get receivable/loan paginate with filters and sorting.
     *
     * @param string|null $search
     * @param string|null $startDate
     * @param string|null $endDate
     * @param string|null $nameFilter
     * @param int|null $branchFilter
     * @param int|null $statusFilter
     * @param string $sortBy
     * @param string $sortDirection
     * @param int $perPage
     * @param int $page
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function execute()
    {
        $isSuperAdmin = Auth::user()->hasRole('Superadmin');
        return Receivable::with(['employee', 'employee.branch', 'approver'])
            ->latest()
            ->when(!$isSuperAdmin && Auth::user()->employee->branch_id != 2, function ($query) {
                $query->whereHas('employee', function ($q) {
                    $q->where('branch_id', Auth::user()->employee->branch_id);
                });
            })
            ->get();
    }
}
