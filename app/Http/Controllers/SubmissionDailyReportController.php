<?php

namespace App\Http\Controllers;

use App\Actions\Data\Submission\DailyReport\GetDailyReportPaginated;
use App\Actions\Data\Submission\DailyReport\GetDailyReportStatistic;
use App\Actions\Data\Submission\DailyReport\GetDailyReportById;
use App\Actions\Data\Submission\DailyReport\UpdateDailyReport;
use App\Actions\Data\Submission\DailyReport\CreateDailyReport;
use App\Actions\Data\Submission\DailyReport\DeleteDailyReport;
use App\Actions\Data\Submission\Referrer\GetBrancByRole;
use App\Enums\DailyReportEnum;
use App\Enums\SubmissionTypeEnum;
use App\Http\Resources\DailyReport\DailyReportListResource;
use App\Http\Resources\DailyReport\DailyReportEmployeeDetailResource;
use App\Http\Resources\Karyawan\KaryawanDetailResource;
use App\Http\Resources\Karyawan\KaryawanListResource;
use App\Models\Branch;
use App\Models\DailyReport;
use App\Models\Employee;
use App\Models\Submission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class SubmissionDailyReportController extends Controller
{
    public function index(Request $request)
    {
        $sortBy = $request->get('sort_by', 'created_at');
        $sortDirection = $request->get('sort_direction', 'desc');

        $search = $request->input('search');
        $perPage = $request->input('per_page', 10);
        $page = $request->input('page', 1);

        // Filter parameters
        $startDateFilter = $request->input('start_date');
        $endDateFilter = $request->input('end_date');
        $nameFilter = $request->input('name_filter');
        $branchFilter = $request->input('branch_filter');
        $typeFilter = SubmissionTypeEnum::EMPLOYEE->value;
        $startDateFilter = $request->input('start_date');
        $statusFilter = $request->input('status_filter');
        if (!Auth::user()->hasRole('Superadmin') && Auth::user()->employee->branch_id != 2) {
            $branchFilter = Auth::user()->employee->branch_id;
        }

        $data = app(GetDailyReportPaginated::class)->execute($search, $startDateFilter, $endDateFilter, $nameFilter, $branchFilter, $typeFilter, $statusFilter, $sortBy, $sortDirection, $perPage, $page);

        $statistics = app(GetDailyReportStatistic::class)->execute($startDateFilter, $endDateFilter, $typeFilter, $nameFilter, $branchFilter, $statusFilter);

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

        $submissionStatuses = [
            ['value' => 'pending', 'label' => 'Menunggu'],
            ['value' => 'approved', 'label' => 'Disetujui'],
            ['value' => 'rejected', 'label' => 'Ditolak'],
        ];

        // Get employees for dropdown
        $employees = Employee::with('branch')->select('id', 'name', 'branch_id')->get()->map(function ($employee) {
            return [
                'id' => $employee->id,
                'name' => $employee->name,
                'branch_name' => $employee->branch->name ?? 'N/A'
            ];
        });

        return Inertia::render('Admin/Submission/Index', [
            'submissions' => KaryawanListResource::collection($data),
            'statistics' => $statistics,
            'branches' => $branches,
            'employees' => $employees,
            'types' => Submission::TYPES,
            'submission_types' => $submissionTypes,
            'submission_statuses' => $submissionStatuses,
            'type' => 'employee',
            'search' => $search,
            'sort_by' => $sortBy,
            'sort_direction' => $sortDirection,
            'per_page' => $perPage,
            'page' => $page,
            'date_filter' => $startDateFilter,
            'end_date' => $endDateFilter,
            'name_filter' => $nameFilter,
            'branch_filter' => $branchFilter,
            'type_filter' => $typeFilter,
            'status_filter' => $statusFilter,
            'start_date' => $startDateFilter,
        ]);
    }

    public function show(Request $request, $id)
    {
        try {
            $submission = app(GetDailyReportById::class)->execute($id);

            if (!$submission) {
                return response()->json([
                    'success' => false,
                    'message' => 'Pengajuan laporan harian tidak ditemukan'
                ], 404);
            }

            $submissionTypes = collect(SubmissionTypeEnum::cases())->map(function ($type) {
                return [
                    'value' => $type->value,
                    'label' => $type->label(),
                ];
            });

            $submissionStatuses = [
                ['value' => 'pending', 'label' => 'Menunggu'],
                ['value' => 'approved', 'label' => 'Disetujui'],
                ['value' => 'rejected', 'label' => 'Ditolak'],
            ];

            // Check if request expects JSON (API call)
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json([
                    'success' => true,
                    'data' => new KaryawanDetailResource($submission),
                    'submission_types' => $submissionTypes,
                    'submission_statuses' => $submissionStatuses,
                    'type' => 'daily-report'
                ]);
            }

            // Return Inertia response for regular page requests
            return Inertia::render('Admin/Submission/components/DetailDailyReport', [
                'submission' => new KaryawanDetailResource($submission),
                'type' => 'daily-report',
                'submission_types' => $submissionTypes,
                'submission_statuses' => $submissionStatuses
            ]);
        } catch (\Exception $e) {
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Terjadi kesalahan saat mengambil data pengajuan laporan harian',
                    'error' => $e->getMessage()
                ], 500);
            }

            throw $e;
        }
    }

    public function store(Request $request)
    {
        $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'name' => 'required|string|max:255',
            'salary' => 'required|numeric|min:0',
            'reason' => 'required|string|max:1000',
            'status' => 'nullable|in:pending,approved,rejected',
            'notes' => 'nullable|string|max:1000',
        ]);

        $dailyReport = app(CreateDailyReport::class)->execute($request->all() + ['employee_id' => Auth::user()->employee->id]);

        if ($request->expectsJson() || $request->is('api/*')) {
            return response()->json([
                'success' => true,
                'message' => 'Pengajuan laporan harian berhasil dibuat',
                'data' => new KaryawanDetailResource($dailyReport)
            ], 201);
        }

        return redirect()->back()->with('success', 'Pengajuan laporan harian berhasil dibuat!');
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'name' => 'nullable|string|max:255',
            'salary' => 'nullable|numeric|min:0',
            'reason' => 'nullable|string|max:1000',
            'status' => 'nullable|in:pending,approved,rejected',
            'notes' => 'nullable|string|max:1000',
        ]);



        $submission = DailyReport::find($id);

        if (!$submission) {
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Pengajuan tidak ditemukan'
                ], 404);
            }
            abort(404, 'Pengajuan tidak ditemukan');
        }

        app(UpdateDailyReport::class)->execute($request->all(), $submission);

        if ($request->expectsJson() || $request->is('api/*')) {
            return response()->json([
                'success' => true,
                'message' => 'Pengajuan laporan harian berhasil diperbarui',
                'data' => new KaryawanDetailResource($submission->fresh())
            ]);
        }

        return redirect()->back()->with('success', 'Pengajuan berhasil diperbarui!');
    }

    public function destroy(Request $request, $id)
    {
        $submission = DailyReport::find($id);

        if (!$submission) {
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Pengajuan tidak ditemukan'
                ], 404);
            }
            abort(404, 'Pengajuan tidak ditemukan');
        }

        // Cek apakah status sudah disetujui atau ditolak
        if (
            $submission->status === DailyReportEnum::APPROVED ||
            $submission->status === DailyReportEnum::REJECTED
        ) {
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Pengajuan yang sudah disetujui atau ditolak tidak dapat dihapus'
                ], 403);
            }
            return redirect()->back()->with('error', 'Pengajuan yang sudah disetujui atau ditolak tidak dapat dihapus!');
        }

        app(DeleteDailyReport::class)->execute($submission);

        if ($request->expectsJson() || $request->is('api/*')) {
            return response()->json([
                'success' => true,
                'message' => 'Pengajuan laporan harian berhasil dihapus'
            ]);
        }

        return redirect()->back()->with('success', 'Pengajuan berhasil dihapus!');
    }
}
