<?php

namespace App\Http\Controllers;

use App\Actions\Data\Submission\GetSubmissionPaginate;
use App\Actions\Data\Submission\Referrer\GetBrancByRole;
use App\Actions\Data\Submission\Statistics;
use App\Actions\Data\Submission\Sick\GetSubmissionSickPaginate;
use App\Actions\Data\Submission\Sick\GetStatistic;
use App\Actions\Data\Submission\Sick\GetSubmissionById;
use App\Actions\Data\Submission\Sick\UpdateSubmissionSick;
use App\Enums\SubmissionTypeEnum;
use App\Enums\SubmissionStatusEnum;
use App\Enums\EmployeeLeaveRequestStatusEnum;
use App\Http\Resources\Sick\SickEmployeeDetailResource;
use App\Http\Resources\Sick\SickListResource;
use Illuminate\Http\Request;
use App\Http\Resources\SubmissionResource;
use App\Models\Branch;
use App\Models\EmployeeLeaveRequest;
use App\Models\LeaveType;
use App\Models\Submission;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class SubmissionOtherController extends Controller
{
    public function index(Request $request)
    {
        $sortBy = $request->get('sort_by', 'submission_date');
        $sortDirection = $request->get('sort_direction', 'asc');

        $search = $request->input('search');
        $perPage = $request->input('per_page', 10);
        $page = $request->input('page', 1);

        // New filter parameters
        $startDateFilter = $request->input('start_date');
        $endDateFilter = $request->input('end_date');
        $nameFilter = $request->input('name_filter');
        $branchFilter = $request->input('branch_filter');
        $typeFilter = 'special_leave';

        $startDateFilter = $request->input('start_date');
        $statusFilter = $request->input('status_filter');

        if (!Auth::user()->hasRole('Superadmin') && Auth::user()->employee->branch_id != 2) {
            $branchFilter = Auth::user()->employee->branch_id;
        }

        $data = app(GetSubmissionSickPaginate::class)->execute($search, $startDateFilter, $endDateFilter, $nameFilter, $branchFilter, $typeFilter, $statusFilter, $sortBy, $sortDirection, $perPage, $page);

        $statistics = app(GetStatistic::class)->execute($startDateFilter, $endDateFilter, $typeFilter, $nameFilter, $branchFilter, $statusFilter);
        // Get filter options

        $typeFilter = SubmissionTypeEnum::SICK->value;
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

        // dd($submissionStatuses);

        return Inertia::render('Admin/Submission/Index', [
            'submissions' => SickListResource::collection($data)->response()->getData(true),
            'sort_by' => $sortBy,
            'sort_direction' => $sortDirection,
            'search' => $search,
            'start_date' => $startDateFilter,
            'end_date' => $endDateFilter,
            'name_filter' => $nameFilter,
            'branch_filter' => $branchFilter,
            'start_date' => $startDateFilter,
            'type_filter' => SubmissionTypeEnum::OTHERS->value,
            'status_filter' => $statusFilter,
            'branches' => $branches,
            'submission_types' => $submissionTypes,
            'submission_statuses' => $submissionStatuses,
            'statistics' => $statistics,
            'type' => "others",
            'types' => Submission::TYPES
        ]);
    }

    public function show(Request $request, $id)
    {
        try {
            $submission = app(GetSubmissionById::class)->execute($id, SubmissionTypeEnum::OTHERS->value);

            if (!$submission) {
                return response()->json([
                    'success' => false,
                    'message' => 'Pengajuan tidak ditemukan'
                ], 404);
            }

            $submissionTypes = collect(SubmissionTypeEnum::cases())->map(function ($type) {
                return [
                    'value' => $type->value,
                    'label' => $type->label(),
                ];
            });

            $submissionStatuses = collect(EmployeeLeaveRequestStatusEnum::cases())->map(function ($status) {
                return [
                    'value' => $status->value,
                    'label' => $status->label(),
                ];
            });

            // Check if request expects JSON (API call)
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json([
                    'success' => true,
                    'data' => new SickEmployeeDetailResource($submission),
                    'submission_types' => $submissionTypes,
                    'submission_statuses' => $submissionStatuses,
                    'type' => 'others'
                ]);
            }

            // Return Inertia response for regular page requests
            return Inertia::render('Admin/Submission/components/DetailOther', [
                'submission' => new SickEmployeeDetailResource($submission),
                'type' => 'others',
                'submission_types' => $submissionTypes,
                'submission_statuses' => $submissionStatuses
            ]);
        } catch (\Exception $e) {
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Terjadi kesalahan saat mengambil data pengajuan',
                    'error' => $e->getMessage()
                ], 500);
            }

            throw $e;
        }
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'status' => 'required',
            'admin_notes' => 'nullable|string|max:1000',
        ]);

        $submission = EmployeeLeaveRequest::find($id);

        if (!$submission) {
            abort(404, 'Pengajuan tidak ditemukan');
        }


        $result = app(UpdateSubmissionSick::class)->execute($request->all(), $submission);
        if ($result) {
            return redirect()->back()->with('error', $result['message']);
        }

        return redirect()->back()->with('success', 'Pengajuan berhasil diperbarui!');
    }

    public function correct(Request $request, $id)
    {
        $request->validate([
            'start_date'       => 'required|date',
            'end_date'         => 'required|date|after_or_equal:start_date',
            'correction_reason' => 'nullable|string|max:500',
        ]);

        $submission = EmployeeLeaveRequest::find($id);

        if (!$submission) {
            abort(404, 'Pengajuan tidak ditemukan');
        }

        $submission->update([
            'corrected_start_date' => $request->start_date,
            'corrected_end_date'   => $request->end_date,
            'corrected_by'         => Auth::user()->employee->id,
            'corrected_at'         => now(),
            'correction_reason'    => $request->correction_reason,
        ]);

        return redirect()->back()->with('success', 'Tanggal pengajuan berhasil dikoreksi!');
    }
}
