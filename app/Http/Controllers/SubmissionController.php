<?php

namespace App\Http\Controllers;

use App\Actions\Data\Submission\GetSubmissionPaginate;
use App\Actions\Data\Submission\Statistics;
use App\Enums\EmployeeLeaveRequestStatusEnum;
use App\Enums\SubmissionStatusEnum;
use App\Enums\SubmissionTypeEnum;
use App\Models\Submission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use App\Http\Resources\SubmissionAllResource;
use App\Models\Branch;

use App\Actions\Data\Submission\Referrer\GetBrancByRole;

class SubmissionController extends Controller
{
    /**
     * Display a listing of the submissions.
     */
    public function index(Request $request)
    {
        $sortBy = $request->get('sort_by', 'created_at');
        $sortDirection = $request->get('sort_direction', 'desc');
        $search = $request->input('search');
        $perPage = $request->input('per_page', 10);

        // New filter parameters
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');
        $nameFilter = $request->input('name_filter');
        $typeFilter = $request->input('type_filter');
        $statusFilter = $request->input('status_filter');

        if (!Auth::user()->hasRole('Superadmin') && Auth::user()->employee->branch_id != 2) {
            $branchFilter = Auth::user()->employee->branch_id;
        } else {
            $branchFilter = $request->input('branch_filter');
        }

        $data =  app(GetSubmissionPaginate::class)->execute($search, $startDate, $endDate, $nameFilter, $branchFilter, $typeFilter, $statusFilter, $sortBy, $sortDirection, $perPage);

        // Get filter options
        $branches = app(GetBrancByRole::class)->execute(Auth::user());
        $submissionTypes = collect(SubmissionTypeEnum::cases())->map(function ($type) {
            return [
                'value' => $type->value,
                'label' => $type->label()['label'],
                'name' => $type->label()['name'],
                'permission' => $type->label()['permission'],
            ];
        });


        $submissionStatuses = collect(EmployeeLeaveRequestStatusEnum::cases())->map(function ($status) {
            return [
                'value' => $status->value,
                'label' => $status->label(),
            ];
        });

        return Inertia::render('Admin/Submission/Index', [
            'submissions' => SubmissionAllResource::collection($data)->response()->getData(true),
            'sort_by' => $sortBy,
            'sort_direction' => $sortDirection,
            'search' => $search,
            'start_date' => $startDate,
            'end_date' => $endDate,
            'name_filter' => $nameFilter,
            'branch_filter' => $branchFilter,
            'type_filter' => $typeFilter,
            'status_filter' => $statusFilter,
            'branches' => $branches,
            'submission_types' => $submissionTypes,
            'submission_statuses' => $submissionStatuses,
            'statistics' => app(Statistics::class)->data(),
            'type' => "submissions",
            'types' => Submission::TYPES
        ]);
    }
}
