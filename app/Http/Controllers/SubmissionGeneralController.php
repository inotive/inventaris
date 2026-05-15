<?php

namespace App\Http\Controllers;

use App\Actions\Data\Submission\General\GetGeneralSubmissionPaginate;
use App\Actions\Data\Submission\General\UpdateGeneralSubmission;
use App\Actions\Data\Submission\General\UpdateStatusGeneralSubmission;
use App\Actions\Data\Submission\Referrer\GetBrancByRole;
use App\Actions\Data\Submission\Statistics;
use App\Enums\EmployeeGeneralStatusEnum;
use App\Enums\EmployeeLeaveRequestStatusEnum;
use App\Enums\SubmissionStatusEnum;
use App\Enums\SubmissionTypeEnum;
use App\Http\Requests\Submission\UpdateStatusGeneralRequest;
use App\Http\Resources\SubmissionGeneralResource;
use App\Models\GeneralSubmission;
use App\Models\Submission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class SubmissionGeneralController extends Controller
{
    public function index(Request $request)
    {
        $sortBy = $request->get('sort_by', 'created_at');
        $sortDirection = $request->get('sort_direction', 'desc');
        $search = $request->input('search');
        $perPage = $request->input('per_page', 10);
        $page = $request->input('page', 1);

        $startDateFilter = $request->input('start_date');
        $endDateFilter = $request->input('end_date');
        $nameFilter = $request->input('name_filter');
        $branchFilter = $request->input('branch_filter');
        $statusFilter = $request->input('status_filter');

        // dd($statusFilter);

        if (!Auth::user()->hasRole('Superadmin') && Auth::user()->employee->branch_id != 2) {
            $branchFilter = Auth::user()->employee->branch_id;
        }

        $data = app(GetGeneralSubmissionPaginate::class)->execute(
            $search,
            $startDateFilter,
            $endDateFilter,
            $nameFilter,
            $branchFilter,
            $statusFilter,
            $sortBy,
            $sortDirection,
            $perPage,
            $page
        );

        $branches = app(GetBrancByRole::class)->execute(Auth::user());

        $submissionTypes = collect(SubmissionTypeEnum::cases())->map(function ($type) {
            return [
                'value' => $type->value,
                'label' => $type->label()['label'],
                'name' => $type->label()['name'],
                'permission' => $type->label()['permission'],
            ];
        });

        $submissionStatuses = collect(EmployeeGeneralStatusEnum::cases())->map(function ($status) {
            return [
                'value' => $status->value,
                'label' => $status->label(),
            ];
        });


        $statistics = app(\App\Actions\Data\Submission\General\GetStatistic::class)->execute(
            $startDateFilter,
            $endDateFilter,
            $nameFilter,
            $branchFilter,
            $statusFilter
        );

        return Inertia::render('Admin/Submission/Index', [
            'submissions' => SubmissionGeneralResource::collection($data)->response()->getData(true),
            'sort_by' => $sortBy,
            'sort_direction' => $sortDirection,
            'search' => $search,
            'start_date' => $startDateFilter,
            'end_date' => $endDateFilter,
            'name_filter' => $nameFilter,
            'branch_filter' => $branchFilter,
            'status_filter' => $statusFilter,
            'branches' => $branches,
            'submission_types' => $submissionTypes,
            'submission_statuses' => $submissionStatuses,
            'statistics' => $statistics,
            'type' => "general",
            'types' => Submission::TYPES
        ]);
    }

    public function show(Request $request, $id)
    {
        $submission = GeneralSubmission::with(['employee', 'branch', 'approver', 'attachments'])->find($id);

        if (!$submission) {
            return response()->json(['success' => false, 'message' => 'Pengajuan tidak ditemukan'], 404);
        }

        if ($request->expectsJson() || $request->is('api/*')) {
            return response()->json([
                'success' => true,
                'data' => $submission,
                'type' => 'general'
            ]);
        }

        return Inertia::render('Admin/Submission/components/DetailGeneral', [
            'submission' => $submission
        ]);
    }

    public function update(UpdateStatusGeneralRequest $request, $id)
    {
        $submission = GeneralSubmission::findOrFail($id);
        app(UpdateStatusGeneralSubmission::class)->execute(Auth::user(), $submission, $request->validated());

        return redirect()->back()->with('success', 'Pengajuan berhasil diperbarui!');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'tag' => 'required|array',
            'tag.*' => 'string',
            'note' => 'nullable|string',
        ]);

        GeneralSubmission::create([
            'employee_id' => Auth::user()->employee->id,
            'branch_id' => Auth::user()->employee->branch_id,
            'title' => $validated['title'],
            'tag' => $validated['tag'],
            'note' => $validated['note'] ?? null,
            'status' => GeneralSubmission::STATUS_PENDING,
        ]);

        return redirect()->back()->with('success', 'Pengajuan umum berhasil dibuat');
    }

    public function destroy($id)
    {
        $submission = GeneralSubmission::findOrFail($id);

        if ($submission->status !== GeneralSubmission::STATUS_PENDING) {
            return redirect()->back()->with('error', 'Hanya pengajuan status pending yang dapat dihapus');
        }

        $submission->delete();


        return redirect()->back()->with('success', 'Pengajuan berhasil dihapus');
    }
}
