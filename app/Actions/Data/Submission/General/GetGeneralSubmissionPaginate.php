<?php

namespace App\Actions\Data\Submission\General;

use App\Models\GeneralSubmission;
use Illuminate\Database\Eloquent\Builder;

class GetGeneralSubmissionPaginate
{
    public function execute(
        $search = null,
        $startDate = null,
        $endDate = null,
        $nameFilter = null,
        $branchFilter = null,
        $statusFilter = null,
        $sortBy = 'created_at',
        $sortDirection = 'desc',
        $perPage = 10,
        $page = 1
    ) {
        return GeneralSubmission::query()
            ->with(['employee', 'branch', 'approver'])
            ->when($search, function (Builder $query) use ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('title', 'like', "%{$search}%")
                        ->orWhereHas('employee', function ($q) use ($search) {
                            $q->where('name', 'like', "%{$search}%");
                        });
                });
            })
            ->when($startDate, function (Builder $query) use ($startDate) {
                $query->whereDate('created_at', '>=', $startDate);
            })
            ->when($endDate, function (Builder $query) use ($endDate) {
                $query->whereDate('created_at', '<=', $endDate);
            })
            ->when($nameFilter, function (Builder $query) use ($nameFilter) {
                $query->whereHas('employee', function ($q) use ($nameFilter) {
                    $q->where('name', 'like', "%{$nameFilter}%");
                });
            })
            ->when($branchFilter, function (Builder $query) use ($branchFilter) {
                $query->where('branch_id', $branchFilter);
            })
            ->when($statusFilter, function (Builder $query) use ($statusFilter) {
                $query->where('status', $statusFilter);
            })
            ->orderBy($sortBy, $sortDirection)
            ->paginate($perPage, ['*'], 'page', $page);
    }
}
