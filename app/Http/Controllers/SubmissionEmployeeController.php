<?php

namespace App\Http\Controllers;

use App\Actions\Data\Submission\DailyReport\GetDailyReportStatistic;
use App\Actions\Data\Submission\GetSubmissionPaginate;
use App\Actions\Data\Submission\Statistics;
use App\Enums\SubmissionStatusEnum;
use App\Enums\SubmissionTypeEnum;
use App\Http\Resources\SubmissionResource;
use App\Models\Branch;
use App\Models\Submission;
use Inertia\Inertia;
use Illuminate\Http\Request;

class SubmissionEmployeeController extends Controller
{
    public function index(Request $request){
        $sortBy = $request->get('sort_by', 'submission_date');
        $sortDirection = $request->get('sort_direction', 'asc');

        $search = $request->input('search');
        $perPage = $request->input('per_page', 10);

        // New filter parameters
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');
        $nameFilter = $request->input('name_filter');
        $branchFilter = $request->input('branch_filter');
        $typeFilter = SubmissionTypeEnum::EMPLOYEE->value;
        $statusFilter = $request->input('status_filter');

        $data =  app(GetSubmissionPaginate::class)->execute($search, $startDate, $endDate, $nameFilter, $branchFilter, $typeFilter, $statusFilter, $sortBy, $sortDirection, $perPage);

        $statistics = app(GetDailyReportStatistic::class)->execute($startDate, $endDate, $typeFilter, $nameFilter, $branchFilter, $statusFilter);
        // Get filter options
        $branches = Branch::select('id', 'name')->get();
        $submissionTypes = collect(SubmissionTypeEnum::cases())->map(function ($type) {
            return [
                'value' => $type->value,
                'label' => $type->label()['label'],
                'name' => $type->label()['name'],
                'permission' => $type->label()['permission'],
            ];
        });
        $submissionStatuses = collect(SubmissionStatusEnum::cases())->map(function ($status) {
            return [
                'value' => $status->value,
                'label' => $status->label(),
            ];
        });

        return Inertia::render('Admin/Submission/Index', [
            'submissions' => SubmissionResource::collection($data),
            'sort_by' => $sortBy,
            'sort_direction' => $sortDirection,
            'search' => $search,
            'start_date' => $startDate,
            'end_date' => $endDate,
            'name_filter' => $nameFilter,
            'branch_filter' => $branchFilter,
            'type_filter' => SubmissionTypeEnum::EMPLOYEE->value,
            'status_filter' => $statusFilter,
            'branches' => $branches,
            'submission_types' => $submissionTypes,
            'submission_statuses' => $submissionStatuses,
            'statistics' => $statistics,
            'type' => "employee",
            'types' => Submission::TYPES
        ]);
    }
}
