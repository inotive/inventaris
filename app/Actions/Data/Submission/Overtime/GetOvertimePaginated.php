<?php

namespace App\Actions\Data\Submission\Overtime;

use App\Models\EmployeeOvertime;

class GetOvertimePaginated
{
    /**
     * Get overtime paginate based on filters.
     *
     * @param string|null $search
     * @param string|null $dateFilter
     * @param string|null $nameFilter
     * @param string|null $branchFilter
     * @param string|null $typeFilter
     * @param string|null $statusFilter
     * @param string $sortBy
     * @param string $sortDirection
     * @param int $perPage
     * @param int $page
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function execute($search, $startDate, $endDate, $nameFilter, $branchFilter, $typeFilter, $statusFilter, $sortBy, $sortDirection, $perPage, $page)
    {
        $data = EmployeeOvertime::with(['employee', 'employee.branch', 'approved'])
                ->when($search, function ($query, $search) {
                    $query->whereHas('employee', function ($q) use ($search) {
                        $q->where('name', 'like', '%' . $search . '%');
                    })->orWhereHas('employee.branch', function ($q) use ($search) {
                        $q->where('name', 'like', '%' . $search . '%');
                    });
                })
                ->when($startDate, function ($query, $startDate) {
                    $query->whereDate('created_at', '>=', $startDate);
                })
                ->when($endDate, function ($query, $endDate) {
                    $query->whereDate('created_at', '<=', $endDate);
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
                })
                ->orderBy($sortBy, $sortDirection)
                ->paginate($perPage, ['*'], 'page', $page)->withQueryString();

        return $data;
    }
}
