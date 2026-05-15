<?php

namespace App\Actions\Data\Submission\Loan;

use App\Models\Receivable;


class GetStatisticLoan
{
    /**
     * Get submission paginate based on type and status.
     *
     * @param string|null $type
     * @return array
     */
    public function execute($startDateFilter, $endDateFilter, $typeFilter, $nameFilter, $branchFilter, $statusFilter)
    {
        // dd($dateFilter);
        $data = Receivable::query()
            ->selectRaw('status, COUNT(id) as total')
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
            })
            ->groupBy('status')
            ->pluck('total', 'status')
            ->toArray();

        return $data;
    }
}
