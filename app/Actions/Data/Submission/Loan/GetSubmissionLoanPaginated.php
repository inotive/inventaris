<?php

namespace App\Actions\Data\Submission\Loan;

use App\Enums\ReceivableStatusEnum;
use App\Models\Receivable;

class GetSubmissionLoanPaginated
{
    /**
     * Get receivable/loan paginate with filters and sorting.
     *
     * @param string|null $search
     * @param string|null $dateFilter
     * @param string|null $nameFilter
     * @param int|null $branchFilter
     * @param int|null $statusFilter
     * @param string $sortBy
     * @param string $sortDirection
     * @param int $perPage
     * @param int $page
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function execute($search, $startDateFilter, $endDateFilter, $nameFilter, $branchFilter, $typeFilter, $statusFilter, $sortBy, $sortDirection, $perPage, $page)
    {
        $query = Receivable::with(['employee', 'employee.branch', 'approver','payments'])
            ->when($search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->whereHas('employee', function ($subQuery) use ($search) {
                        $subQuery->where('name', 'like', '%' . $search . '%')
                                ->orWhere('staff_id', 'like', '%' . $search . '%');
                    })->orWhereHas('employee.branch', function ($subQuery) use ($search) {
                        $subQuery->where('name', 'like', '%' . $search . '%');
                    })->orWhere('note', 'like', '%' . $search . '%');
                });
            })
            ->when($startDateFilter, function ($query, $startDateFilter) {
                $query->whereDate('date', '>=', $startDateFilter);
            })
            ->when($endDateFilter, function ($query, $endDateFilter) {
                $query->whereDate('date', '<=', $endDateFilter);
            })
            ->when($nameFilter, function ($query, $nameFilter) {
                $query->whereHas('employee', function ($q) use ($nameFilter) {
                    $q->where('name', 'like', '%' . $nameFilter . '%');
                });
            })
            ->when($branchFilter, function ($query, $branchFilter) {
                $query->whereHas('employee.branch', function ($q) use ($branchFilter) {
                    $q->where('id', $branchFilter);
                });
            })
            ->when($statusFilter, function ($query, $statusFilter) {
                $query->where('status', $statusFilter);
            });

        // Apply sorting
        $allowedSortFields = ['date', 'amount', 'status', 'created_at'];
        $sortField = in_array($sortBy, $allowedSortFields) ? $sortBy : 'created_at';
        $sortDir = in_array(strtolower($sortDirection), ['asc', 'desc']) ? $sortDirection : 'desc';

        $query->orderBy($sortField, $sortDir);

        return $query->paginate($perPage, ['*'], 'page', $page)->withQueryString();
    }
}
