<?php

namespace App\Actions\Data\Submission\Overtime;

use App\Models\EmployeeOvertime;

class GetOvertimeStatistic
{
    /**
     * Get overtime statistics.
     *
     * @param string|null $dateFilter
     * @param string|null $typeFilter
     * @param string|null $nameFilter
     * @param string|null $branchFilter
     * @param string|null $statusFilter
     * @return array
     */
    public function execute($startDateFilter, $endDateFilter, $typeFilter, $nameFilter, $branchFilter, $statusFilter)
    {
        $query = EmployeeOvertime::with(['employee', 'employee.branch'])
        ->when($startDateFilter, function ($query, $startDateFilter) {
            $query->whereDate('created_at', '>=', $startDateFilter);
        })
        ->when($endDateFilter, function ($query, $endDateFilter) {
            $query->whereDate('created_at', '<=', $endDateFilter);
        });

        if ($nameFilter) {
            $query->whereHas('employee', function ($q) use ($nameFilter) {
                $q->where('name', 'like', '%' . $nameFilter . '%');
            });
        }

        if ($branchFilter) {
            $query->whereHas('employee.branch', function ($q) use ($branchFilter) {
                $q->where('id', $branchFilter);
            });
        }

        if ($statusFilter) {
            $query->where('status', $statusFilter);
        }

        $total = $query->count();
        $pending = (clone $query)->where('status', 'pending')->count();
        $approved = (clone $query)->where('status', 'approved')->count();
        $rejected = (clone $query)->where('status', 'rejected')->count();

        return [
            'total' => $total,
            'pending' => $pending,
            'approved' => $approved,
            'rejected' => $rejected,
        ];
    }
}
