<?php

namespace App\Actions\Data\Submission\General;

use App\Models\GeneralSubmission;

class GetStatistic
{
    /**
     * Get submission paginate based on status.
     *
     * @return array
     */
    public function execute($startDateFilter, $endDateFilter, $nameFilter, $branchFilter, $statusFilter)
    {
        $data = GeneralSubmission::query()
            ->selectRaw('status, COUNT(id) as total')
            ->when($startDateFilter, function ($query, $startDateFilter) {
                $query->whereDate('created_at', '>=', $startDateFilter);
            })
            ->when($endDateFilter, function ($query, $endDateFilter) {
                $query->whereDate('created_at', '<=', $endDateFilter);
            })
            ->when($nameFilter, function ($query, $nameFilter) {
                $query->whereHas('employee', function ($q) use ($nameFilter) {
                    $q->where('name', 'like', '%' . $nameFilter . '%');
                });
            })
            ->when($branchFilter, function ($query) use ($branchFilter) {
                $query->where('branch_id', $branchFilter);
            })
            ->when($statusFilter, function ($query, $statusFilter) {
                $query->where('status', $statusFilter);
            })
            ->groupBy('status')
            ->pluck('total', 'status')
            ->toArray();

        return $data;
    }
}
