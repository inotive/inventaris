<?php

namespace App\Actions\Data\Submission\Sick;

use App\Enums\SubmissionStatusEnum;
use App\Models\EmployeeLeaveRequest;
use App\Models\Submission;


class GetSubmissionSickPaginate
{
    /**
     * Get submission paginate based on type and status.
     *
     * @param string|null $type
     * @return array
     */
    public function execute($search, $startDate, $endDate, $nameFilter, $branchFilter, $typeFilter, $statusFilter, $sortBy, $sortDirection, $perPage, $page)
    {
        $data = EmployeeLeaveRequest::with(['employee','employee.branch', 'leaveType', 'approver'])
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
                ->when($typeFilter, function ($query, $typeFilter) {
                    $query->whereHas('leaveType', function ($q) use ($typeFilter) {
                        $q->where('category', $typeFilter);
                    });
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
                ->latest()
                ->paginate($perPage, ['*'], 'page', $page)->withQueryString();

        return $data;
    }
}
