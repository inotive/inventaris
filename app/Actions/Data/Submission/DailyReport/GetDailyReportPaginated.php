<?php

namespace App\Actions\Data\Submission\DailyReport;

use App\Models\DailyReport;
use Illuminate\Pagination\LengthAwarePaginator;

class GetDailyReportPaginated
{
    public function execute($search, $startDate, $endDate, $nameFilter, $branchFilter, $typeFilter, $statusFilter, $sortBy, $sortDirection, $perPage, $page)
    {
        $query = DailyReport::with(['employee.branch', 'employee.department', 'approved'])
            ->when($search, function ($q) use ($search) {
                $q->whereHas('employee', function ($employeeQuery) use ($search) {
                    $employeeQuery->where('name', 'like', "%{$search}%");
                });
            })
            ->when($nameFilter, function ($q) use ($nameFilter) {
                $q->whereHas('employee', function ($employeeQuery) use ($nameFilter) {
                    $employeeQuery->where('name', 'like', "%{$nameFilter}%");
                });
            })
            ->when($branchFilter, function ($q) use ($branchFilter) {
                $q->whereHas('employee', function ($employeeQuery) use ($branchFilter) {
                    $employeeQuery->where('branch_id', $branchFilter);
                });
            })
            ->when($statusFilter, function ($q) use ($statusFilter) {
                $q->where('status', $statusFilter);
            })
            ->when($startDate, function ($q) use ($startDate) {
                $q->whereDate('created_at', '>=', $startDate);
            })
            ->when($endDate, function ($q) use ($endDate) {
                $q->whereDate('created_at', '<=', $endDate);
            });


        return $query->orderBy('created_at', 'desc')->paginate($perPage)->withQueryString();
    }
}
