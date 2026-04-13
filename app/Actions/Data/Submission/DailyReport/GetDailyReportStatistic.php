<?php

namespace App\Actions\Data\Submission\DailyReport;

use App\Models\DailyReport;

class GetDailyReportStatistic
{
    public function execute($startDate, $endDate, $typeFilter, $nameFilter, $branchFilter, $statusFilter)
    {
        $query = DailyReport::query()
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
            ->when($startDate, function ($q) use ($startDate) {
                $q->whereDate('created_at', '>=', $startDate);
            })
            ->when($endDate, function ($q) use ($endDate) {
                $q->whereDate('created_at', '<=', $endDate);
            })
            ->when($statusFilter, function ($q) use ($statusFilter) {
                $q->where('status', $statusFilter);
            });

        return [
            'pending' => (clone $query)->where('status', 'pending')->count(),
            'approved' => (clone $query)->where('status', 'approved')->count(),
            'rejected' => (clone $query)->where('status', 'rejected')->count(),
            'total' => $query->count(),
        ];
    }
}
