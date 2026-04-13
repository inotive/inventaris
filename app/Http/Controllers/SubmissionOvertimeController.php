<?php

namespace App\Http\Controllers;

use App\Actions\Data\Submission\Overtime\GetOvertimePaginated;
use App\Actions\Data\Submission\Overtime\GetOvertimeStatistic;
use App\Actions\Data\Submission\Overtime\GetOvertimeById;
use App\Actions\Data\Submission\Overtime\UpdateOvertime;
use App\Actions\Data\Submission\Referrer\GetBrancByRole;
use App\Enums\EmployeeOvertimeStatusEnum;
use App\Enums\SubmissionTypeEnum;
use App\Http\Resources\Overtime\OvertimeEmployeeDetailResource;
use App\Http\Resources\Overtime\OvertimeListResource;
use App\Models\Branch;
use App\Models\EmployeeOvertime;
use App\Models\Submission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class SubmissionOvertimeController extends Controller
{
    public function index(Request $request)
    {
        $sortBy = $request->get('sort_by', 'created_at');
        $sortDirection = $request->get('sort_direction', 'desc');

        $search = $request->input('search');
        $perPage = $request->input('per_page', 10);
        $page = $request->input('page', 1);

        // New filter parameters
        $startDateFilter = $request->input('start_date');
        $endDateFilter = $request->input('end_date');
        $nameFilter = $request->input('name_filter');
        $branchFilter = $request->input('branch_filter');
        $typeFilter = SubmissionTypeEnum::OVERTIME->value;
        $startDateFilter = $request->input('start_date');
        $statusFilter = $request->input('status_filter');

        if (!Auth::user()->hasRole('Superadmin') && Auth::user()->employee->branch_id != 2) {
            $branchFilter = Auth::user()->employee->branch_id;
        }

        $data = app(GetOvertimePaginated::class)->execute($search, $startDateFilter, $endDateFilter, $nameFilter, $branchFilter, $typeFilter, $statusFilter, $sortBy, $sortDirection, $perPage, $page);

        $statistics = app(GetOvertimeStatistic::class)->execute($startDateFilter, $endDateFilter, $typeFilter, $nameFilter, $branchFilter, $statusFilter);

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

        $submissionStatuses = collect(EmployeeOvertimeStatusEnum::cases())->map(function ($status) {
            return [
                'value' => $status->value,
                'label' => $status->label(),
            ];
        });

        return Inertia::render('Admin/Submission/Index', [
            'submissions' => OvertimeListResource::collection($data)->response()->getData(true),
            'sort_by' => $sortBy,
            'sort_direction' => $sortDirection,
            'search' => $search,
            'start_date' => $startDateFilter,
            'end_date' => $endDateFilter,
            'name_filter' => $nameFilter,
            'branch_filter' => $branchFilter,
            'start_date' => $startDateFilter,
            'type_filter' => SubmissionTypeEnum::OVERTIME->value,
            'status_filter' => $statusFilter,
            'branches' => $branches,
            'submission_types' => $submissionTypes,
            'submission_statuses' => $submissionStatuses,
            'statistics' => $statistics,
            'type' => "overtime",
            'types' => Submission::TYPES
        ]);
    }

    public function show(Request $request, $id)
    {
        try {
            $submission = app(GetOvertimeById::class)->execute($id);

            if (!$submission) {
                return response()->json([
                    'success' => false,
                    'message' => 'Pengajuan lembur tidak ditemukan'
                ], 404);
            }

            $submissionTypes = collect(SubmissionTypeEnum::cases())->map(function ($type) {
                return [
                    'value' => $type->value,
                    'label' => $type->label(),
                ];
            });

            $submissionStatuses = collect(EmployeeOvertimeStatusEnum::cases())->map(function ($status) {
                return [
                    'value' => $status->value,
                    'label' => $status->label(),
                ];
            });

            // Check if request expects JSON (API call)
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json([
                    'success' => true,
                    'data' => new OvertimeEmployeeDetailResource($submission),
                    'submission_types' => $submissionTypes,
                    'submission_statuses' => $submissionStatuses,
                    'type' => 'overtime'
                ]);
            }

            // Return Inertia response for regular page requests
            return Inertia::render('Admin/Submission/components/DetailOvertime', [
                'submission' => new OvertimeEmployeeDetailResource($submission),
                'type' => 'overtime',
                'submission_types' => $submissionTypes,
                'submission_statuses' => $submissionStatuses
            ]);
        } catch (\Exception $e) {
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Terjadi kesalahan saat mengambil data pengajuan lembur',
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

        $overtime = EmployeeOvertime::find($id);

        if (!$overtime) {
            abort(404, 'Pengajuan lembur tidak ditemukan');
        }

        app(UpdateOvertime::class)->execute($request->all(), $overtime);

        return redirect()->back()->with('success', 'Pengajuan lembur berhasil diperbarui!');
    }
}
