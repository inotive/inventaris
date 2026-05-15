<?php

namespace App\Actions\Data\Submission;

use App\Actions\Data\Submission\DailyReport\GetAllDailyReport;
use App\Actions\Data\Submission\Sick\GetSubmissionSickPaginate;
use App\Actions\Data\Submission\Overtime\GetOvertimePaginated;
use App\Actions\Data\Submission\DailyReport\GetDailyReportPaginated;
use App\Actions\Data\Submission\General\GetAllGeneral;
use App\Actions\Data\Submission\Loan\GetAllLoan;
use App\Actions\Data\Submission\Loan\GetSubmissionLoanPaginated;
use App\Actions\Data\Submission\Overtime\GetAllOvertime;
use App\Actions\Data\Submission\Reimbursement\GetAllReimbursement;
use App\Actions\Data\Submission\Sick\GetAllEmployeeLeaveRequest;
use App\Enums\SubmissionTypeEnum;
use App\Models\LeaveType;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class GetSubmissionPaginate
{
    /**
     * Get all submissions paginated by combining data from all submission types.
     *
     * @param string|null $search
     * @param string|null $startDate
     * @param string|null $endDate
     * @param string|null $nameFilter
     * @param string|null $branchFilter
     * @param string|null $typeFilter
     * @param string $sortBy
     * @param string $sortDirection
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function execute()
    {
        $allData = collect();

        $sickLeaveTypes = LeaveType::where('category', 'sick_leave')->pluck('id');
        $annualLeaveTypes = LeaveType::where('category', 'annual_leave')->pluck('id');
        $otherLeaveTypes = LeaveType::where('category', 'special_leave')->pluck('id');
        // Get data from each available submission type
        $submissionTypes = [
            'sick' => app(GetAllEmployeeLeaveRequest::class)->execute($sickLeaveTypes),
            'annual_leave' => app(GetAllEmployeeLeaveRequest::class)->execute($annualLeaveTypes),
            'others' => app(GetAllEmployeeLeaveRequest::class)->execute($otherLeaveTypes),
            'overtime' => app(GetAllOvertime::class)->execute(),
            'employee' => app(GetAllDailyReport::class)->execute(),
            'debt' => app(GetAllLoan::class)->execute(),
            'general' => app(GetAllGeneral::class)->execute(),
            'reimbursement' => app(GetAllReimbursement::class)->execute(),
        ];

        foreach ($submissionTypes as $type => $action) {
            // Skip if type filter is specified and doesn't match
            try {
                // Transform data to include type information
                $transformedData = $action->map(function ($item) use ($type) {
                    $item->submission_type = $type;
                    return $item;
                });

                $allData = $allData->merge($transformedData);
            } catch (\Exception $e) {
                // Skip if action doesn't exist or fails
                continue;
            }
        }

        // Return semua data tanpa paginasi
        return $allData->values();
    }
}
