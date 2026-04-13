<?php

namespace App\Http\Controllers;

use App\Actions\Data\Submission\GetSubmissionPaginate;
use App\Actions\Data\Submission\Loan\GetStatisticLoan;
use App\Actions\Data\Submission\Loan\GetSubmissionLoanPaginated;
use App\Actions\Data\Submission\Referrer\GetBrancByRole;
use App\Actions\Data\Submission\Statistics;
use App\Enums\ReceivableStatusEnum;
use App\Enums\SubmissionStatusEnum;
use App\Enums\SubmissionTypeEnum;
use App\Http\Resources\Debt\ReceivableListResource;
use App\Http\Resources\Debt\ReceivableEmployeeDetailResource;
use App\Http\Resources\SubmissionResource;
use App\Models\Branch;
use App\Models\EmployeeLeaveBalance;
use App\Models\EmployeeReceivableBalance;
use App\Models\Receivable;
use App\Models\Submission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class SubmissionDebtController extends Controller
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
        $typeFilter = SubmissionTypeEnum::DEBT->value;
        $statusFilter = $request->input('status_filter');
        if (!Auth::user()->hasRole('Superadmin') && Auth::user()->employee->branch_id != 2) {
            $branchFilter = Auth::user()->employee->branch_id;
        }

        $data =  app(GetSubmissionLoanPaginated::class)->execute($search, $startDateFilter, $endDateFilter, $nameFilter, $branchFilter, $typeFilter, $statusFilter, $sortBy, $sortDirection, $perPage, $page);

        $statistics = app(GetStatisticLoan::class)->execute($startDateFilter, $endDateFilter, $typeFilter, $nameFilter, $branchFilter, $statusFilter);
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
        $submissionStatuses = collect(ReceivableStatusEnum::cases())->map(function ($status) {
            return [
                'value' => $status->value,
                'label' => $status->label(),
            ];
        });

        return Inertia::render('Admin/Submission/Index', [
            'submissions' => ReceivableListResource::collection($data),
            'sort_by' => $sortBy,
            'sort_direction' => $sortDirection,
            'search' => $search,
            'start_date' => $startDateFilter,
            'end_date' => $endDateFilter,
            'name_filter' => $nameFilter,
            'branch_filter' => $branchFilter,
            'type_filter' => SubmissionTypeEnum::DEBT->value,
            'status_filter' => $statusFilter,
            'branches' => $branches,
            'submission_types' => $submissionTypes,
            'submission_statuses' => $submissionStatuses,
            'statistics' => $statistics,
            'type' => "debt",
            'types' => Submission::TYPES
        ]);
    }

    public function show(Request $request, $id)
    {
        try {
            $receivable = Receivable::with(['employee', 'approver'])->find($id);

            if (!$receivable) {
                return response()->json([
                    'success' => false,
                    'message' => 'Pengajuan pinjaman tidak ditemukan'
                ], 404);
            }

            $submissionTypes = collect(SubmissionTypeEnum::cases())->map(function ($type) {
                return [
                    'value' => $type->value,
                    'label' => $type->label(),
                ];
            });

            $submissionStatuses = collect(ReceivableStatusEnum::cases())->map(function ($status) {
                return [
                    'value' => $status->value,
                    'label' => $status->label(),
                ];
            });

            // Check if request expects JSON (API call)
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json([
                    'success' => true,
                    'data' => new ReceivableEmployeeDetailResource($receivable),
                    'submission_types' => $submissionTypes,
                    'submission_statuses' => $submissionStatuses,
                    'type' => 'debt'
                ]);
            }

            // Return Inertia response for regular page requests
            return Inertia::render('Admin/Submission/components/DetailDebt', [
                'submission' => new ReceivableEmployeeDetailResource($receivable),
                'type' => 'debt',
                'submission_types' => $submissionTypes,
                'submission_statuses' => $submissionStatuses
            ]);
        } catch (\Exception $e) {
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Terjadi kesalahan saat mengambil data pengajuan pinjaman',
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

        $approverId = Auth::user()?->employee?->id;

        if (!$approverId) {
            return redirect()->back()->with('error', 'Data karyawan approver tidak ditemukan.');
        }

        $receivable = Receivable::find($id);

        if (!$receivable) {
            abort(404, 'Pengajuan pinjaman tidak ditemukan');
        }


        if ($request->status === 'approved') {
            $employeeReceivableBalance = EmployeeReceivableBalance::where('employee_id', $receivable->request_by)
            ->where('period_year', now()->year)->first();

            if ($employeeReceivableBalance && $employeeReceivableBalance->remaining_amount < $receivable->amount) {
                return redirect()->back()->with('error', 'Sisa saldo piutang tidak mencukupi.');
            }

            if ($employeeReceivableBalance) {
                $employeeReceivableBalance->update([
                    'used_amount' => $employeeReceivableBalance->used_amount + $receivable->amount,
                    'remaining_amount' => $employeeReceivableBalance->remaining_amount - $receivable->amount,
                ]);
            } else {
                $limitAmount = $receivable->employee->receivableBalance->loan_quota ?? 0;

                if ($limitAmount > 0) {
                    return redirect()->back()->with('error', 'Sisa saldo piutang tidak mencukupi.');
                }

                EmployeeReceivableBalance::create([
                    'employee_id' => $receivable->request_by,
                    'period_year' => now()->year,
                    'period_month' => now()->month,
                    'limit_amount' => $limitAmount,
                    'used_amount' => $receivable->amount,
                    'remaining_amount' => $limitAmount > 0 ? $limitAmount - $receivable->amount : 0,
                ]);
            }
        }


        $receivable->update([
            'status' => $request->status,
            'admin_notes' => $request->admin_notes,
            'approved_by' => $approverId,
            'approved_at' => now(),
        ]);


        return redirect()->back()->with('success', 'Pengajuan pinjaman berhasil diperbarui!');
    }
}
