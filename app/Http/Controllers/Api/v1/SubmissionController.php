<?php

namespace App\Http\Controllers\Api\v1;

use App\Helpers\ResponseFormatter;
use App\Http\Controllers\Controller;
use App\Http\Resources\SubmissionResource;
use App\Models\Submission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Enums\SubmissionStatusEnum;
use Illuminate\Support\Facades\DB;

class SubmissionController extends Controller
{
    /**
     * List submissions for mobile app.
     * SPV/Supervisor can see all submissions; others only see their own.
     * Query params (optional):
     * - q: search by employee name or submission number
     * - status: numeric enum value or label
     * - type: numeric enum value or label
     * - date_from, date_to: YYYY-MM-DD (match submission_date)
     * - per_page: default 15
     *
     * Data sources:
     * - EmployeeLeaveRequest (Cuti)
     * - MaterialRequest (Procurement/Pengadaan)
     * - GoodIssue (Usage/Pemakaian)
     * - Receivable (Loan/Pinjaman)
     * - EmployeeOvertime (Lembur)
     * - GeneralSubmission (General)
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        if (!$user)
            return ResponseFormatter::error('Unauthenticated', 401);

        $status = $request->query('status');
        // `type` here refers to leave type category (e.g. sick_leave, annual_leave)
        $type = $request->query('type');
        $scope = $request->query('scope', 'own'); // 'own' or 'all'
        // Optional: restrict combined list by model type (e.g. EmployeeLeaveRequest, EmployeeOvertime)
        // $model = $request->query('model');
        $leave_type_category = $request->query('leave_type_category');

        // Authorization scope: SPV/Supervisor/Admin can see all, others only own
        $canSeeAll = false;
        if (method_exists($user, 'hasAnyRole')) {
            $canSeeAll = $user->hasAnyRole(['SPV', 'Supervisor', 'Admin', 'Superadmin']);
        } elseif (method_exists($user, 'hasRole')) {
            $canSeeAll = $user->hasRole('SPV') || $user->hasRole('Supervisor') || $user->hasRole('Admin');
        }

        $employeeId = optional($user->employee)->id;
        $branchId = optional($user->employee)->branch_id;

        if (!$canSeeAll && !$employeeId) {
            return ResponseFormatter::error('No employee profile', 422);
        }

        // Logic check: if user is Admin/SPV/Supervisor but NOT Superadmin,
        // they should only see data from their own branch.
        $isSuperAdmin = method_exists($user, 'hasRole') && $user->hasRole('Superadmin');
        $filterByBranch = ($canSeeAll && !$isSuperAdmin && $branchId && $branchId != 2);

        // Collect all submissions into one array
        $submissions = collect();
        // Collect from EmployeeLeaveRequest
        $leaveQuery = \App\Models\EmployeeLeaveRequest::query()
            ->with(['employee:id,name,branch_id,department_id', 'leaveType:id,name,category'])
            ->select('id', 'employee_id', 'leave_type_id', 'status', 'start_date', 'end_date', 'created_at', 'updated_at');

        if ($scope === 'own') {
            $leaveQuery->where('employee_id', $employeeId);
        } else if ($scope === 'all') {
            $leaveQuery->where('employee_id', '!=', $employeeId);
        }

        if ($filterByBranch) {
            $leaveQuery->whereHas('employee', function ($q) use ($branchId) {
                $q->where('branch_id', $branchId);
            });
        }

        if ($status !== null && $status !== '') {
            $leaveQuery->where('status', $status);
        }

        if ($leave_type_category !== null && $leave_type_category !== '') {
            $leaveQuery->whereHas('leaveType', function ($query) use ($leave_type_category) {
                $query->where('category', $leave_type_category);
            });
        }

        $leaveRequests = $leaveQuery->get()->map(function ($item) {
            return [
                'model_type' => 'EmployeeLeaveRequest',
                'model_id' => $item->id,
                'status' => $item->status,
                'created_by' => $item->employee_id,
                'leave_categories' => $item->leaveType->category ?? null,
                'type_label' => $item->leaveType->name ?? 'Unknown',
                'submission_date' => optional($item->start_date)->format('Y-m-d'),
                'end_date' => optional($item->end_date)->format('Y-m-d'),
                'created_at' => $item->created_at,
                'updated_at' => $item->updated_at,
                'employee' => $item->employee,
                'employe_branch' => $item->employee->branch->name
            ];
        });
        // Do not merge yet; we'll merge all sources together below to avoid duplicates
        // Collect from MaterialRequest (Procurement)
        $procurementQuery = \App\Models\MaterialRequest::query()
            ->with(['requester:id,name,branch_id,department_id'])
            ->select('id', 'requested_by', 'status', 'requested_at', 'created_at', 'updated_at');

        if ($scope === 'own') {
            $procurementQuery->where('requested_by', $employeeId);
        } else if ($scope === 'all') {
            $procurementQuery->where('requested_by', '!=', $employeeId);
        }

        if ($filterByBranch) {
            $procurementQuery->whereHas('requester', function ($q) use ($branchId) {
                $q->where('branch_id', $branchId);
            });
        }

        if ($status !== null && $status !== '') {
            $procurementQuery->where('status', $status);
        }

        $procurements = $procurementQuery->get()->map(function ($item) {
            return [
                'model_type' => 'MaterialRequest',
                'model_id' => $item->id,
                'status' => $item->status == "on_progress" ? "pending" : $item->status,
                'created_by' => $item->requested_by,
                'type_label' => 'Permintaan Barang',
                'submission_date' => $item->requested_at,
                'created_at' => $item->created_at,
                'updated_at' => $item->updated_at,
                'employee' => $item->requester,
                'employe_branch' => $item->requester->branch->name
            ];
        });

        // Collect from GoodIssue (Usage)
        $usageQuery = \App\Models\GoodIssue::query()
            ->with(['requestBy:id,name,branch_id,department_id'])
            ->select('id', 'request_by', 'status', 'date', 'created_at', 'updated_at');

        if ($scope === 'own') {
            $usageQuery->where('request_by', $employeeId);
        } else if ($scope === 'all') {
            $usageQuery->where('request_by', '!=', $employeeId);
        }

        if ($filterByBranch) {
            $usageQuery->whereHas('requestBy', function ($q) use ($branchId) {
                $q->where('branch_id', $branchId);
            });
        }

        if ($status !== null && $status !== '') {
            $usageQuery->where('status', $status);
        }

        $usages = $usageQuery->get()->map(function ($item) {
            return [
                'model_type' => 'GoodIssue',
                'model_id' => $item->id,
                'status' => $item->status == "on_progress" ? "pending" : $item->status,
                'created_by' => $item->request_by,
                'type_label' => 'Pengajuan Pemakaian',
                'submission_date' => $item->date,
                'created_at' => $item->created_at,
                'updated_at' => $item->updated_at,
                'employee' => $item->requestBy,
                'employe_branch' => $item->requestBy?->branch?->name
            ];
        });

        // Collect from Receivable (Loan)
        $loanQuery = \App\Models\Receivable::query()
            ->with(['employee:id,name,branch_id,department_id'])
            ->select('id', 'amount', 'date', 'tenor', 'note', 'file_path', 'status', 'request_by', 'approved_by', 'approved_at', 'created_at', 'updated_at');

        if ($scope === 'own') {
            $loanQuery->where('request_by', $employeeId);
        } else if ($scope === 'all') {
            $loanQuery->where('request_by', '!=', $employeeId);
        }

        if ($filterByBranch) {
            $loanQuery->whereHas('employee', function ($q) use ($branchId) {
                $q->where('branch_id', $branchId);
            });
        }

        if ($status !== null && $status !== '') {
            $loanQuery->where('status', $status);
        }

        $loans = $loanQuery->get()->map(function ($item) {
            return [
                'model_type' => 'Receivable',
                'model_id' => $item->id,
                'status' => $item->status == "on_progress" ? "pending" : $item->status,
                'created_by' => $item->request_by,
                'type_label' => 'Pengajuan Pinjaman',
                'amount' => $item->amount,
                'tenor' => $item->tenor,
                'note' => $item->note,
                'file_path' => $item->file_path,
                'approved_by' => $item->approved_by,
                'approved_at' => $item->approved_at,
                'submission_date' => $item->date,
                'created_at' => $item->created_at,
                'updated_at' => $item->updated_at,
                'employee' => $item->employee,
                'employe_branch' => $item->employee->branch->name
            ];
        });

        // Collect from EmployeeOvertime
        $overtimeQuery = \App\Models\EmployeeOvertime::query()
            ->with(['employee:id,name,branch_id,department_id'])
            ->select('id', 'employee_id', 'date', 'start_time', 'end_time', 'duration_hours', 'reason', 'status', 'approved_by', 'notes', 'created_at', 'updated_at');

        if ($scope === 'own') {
            $overtimeQuery->where('employee_id', $employeeId);
        } else if ($scope === 'all') {
            $overtimeQuery->where('employee_id', '!=', $employeeId);
        }

        if ($filterByBranch) {
            $overtimeQuery->whereHas('employee', function ($q) use ($branchId) {
                $q->where('branch_id', $branchId);
            });
        }

        if ($status !== null && $status !== '') {
            $overtimeQuery->where('status', $status);
        }

        $overtimes = $overtimeQuery->get()->map(function ($item) {
            return [
                'model_type' => 'EmployeeOvertime',
                'model_id' => $item->id,
                'status' => $item->status,
                'created_by' => $item->employee_id,
                'type_label' => 'Pengajuan Lembur',
                'start_time' => $item->start_time,
                'end_time' => $item->end_time,
                'duration_hours' => $item->duration_hours,
                'reason' => $item->reason,
                'approved_by' => $item->approved_by,
                'notes' => $item->notes,
                'submission_date' => $item->date,
                'created_at' => $item->created_at,
                'updated_at' => $item->updated_at,
                'employee' => $item->employee,
                'employe_branch' => $item->employee->branch->name
            ];
        });

        // Collect from GeneralSubmission
        $generalQuery = \App\Models\GeneralSubmission::query()
            ->with(['employee:id,name,branch_id,department_id', 'branch:id,name'])
            ->select('id', 'employee_id', 'branch_id', 'title', 'tag', 'status', 'admin_notes', 'approved_by', 'approved_at', 'created_at', 'updated_at');

        if ($scope === 'own') {
            $generalQuery->where('employee_id', $employeeId);
        } else if ($scope === 'all') {
            $generalQuery->where('employee_id', '!=', $employeeId);
        }

        if ($filterByBranch) {
            $generalQuery->where('branch_id', $branchId);
        }

        if ($status !== null && $status !== '') {
            $generalQuery->where('status', $status);
        }

        $generalSubmissions = $generalQuery->get()->map(function ($item) {
            return [
                'model_type' => 'GeneralSubmission',
                'model_id' => $item->id,
                'status' => $item->status,
                'created_by' => $item->employee_id,
                'type_label' => $item->title ?? 'General Submission',
                'submission_date' => $item->created_at->format('Y-m-d'),
                'created_at' => $item->created_at,
                'updated_at' => $item->updated_at,
                'employee' => $item->employee,
                'employe_branch' => $item->branch->name ?? '-',
                'approved_by' => $item->approved_by,
                'approved_at' => $item->approved_at,
                'admin_note' => $item->admin_notes
            ];
        });

        // Merge all submissions into one array (each source merged exactly once)
        $submissions = $submissions
            ->merge($leaveRequests)
            ->merge($procurements)
            ->merge($usages)
            ->merge($loans)
            ->merge($overtimes)
            ->merge($generalSubmissions)
            ->sortByDesc('created_at');

        // Filter by model (if requested). Note: `type` above is used for leave category only.
        if ($type !== null && $type !== '') {
            $submissions = $submissions->filter(function ($item) use ($type) {
                return $item['model_type'] === $type;
            });
        }

        // Sort by submission_date desc
        $submissions = $submissions->sortByDesc('submission_date')->values();

        // Manual pagination
        $total = $submissions->count();
        return ResponseFormatter::success([
            'data' => $submissions,
            'meta' => [
                'total' => $total,
            ],
            'filters' => [
                'scope' => $scope,
                'can_see_all' => $canSeeAll,
                'status' => $status,
                'type' => $type,
                'leave_type_category' => $leave_type_category,
            ]
        ]);
    }

    /**
     * Approve a submission (SPV/Supervisor/Admin only)
     */
    public function approve(Request $request, $id)
    {
        $user = Auth::user();
        if (!$user)
            return ResponseFormatter::error('Unauthenticated', 401);

        $allowed = method_exists($user, 'hasAnyRole')
            ? $user->hasAnyRole(['SPV', 'Supervisor', 'Admin'])
            : ($user->hasRole('SPV') || $user->hasRole('Supervisor') || $user->hasRole('Admin'));
        if (!$allowed)
            return ResponseFormatter::error('Forbidden', 403);

        $submission = Submission::with(['employee:id,name', 'branch'])->find($id);
        if (!$submission)
            return ResponseFormatter::error('Submission not found', 404);
        if ((int) $submission->status === SubmissionStatusEnum::Approved->value) {
            return ResponseFormatter::error('Already approved', 422);
        }
        if ((int) $submission->status === SubmissionStatusEnum::Rejected->value) {
            return ResponseFormatter::error('Already rejected', 422);
        }



        DB::transaction(function () use ($submission, $user) {
            $submission->status = SubmissionStatusEnum::Approved->value;
            $submission->approved_by = $user->id;
            $submission->approved_at = now();
            $submission->rejected_by = null;
            $submission->rejected_at = null;
            $submission->rejected_reason = null;
            $submission->save();
        });

        $submission->refresh();
        return ResponseFormatter::success(new SubmissionResource($submission), 'Approved');
    }

    /**
     * Reject a submission (SPV/Supervisor/Admin only)
     */
    public function reject(Request $request, $id)
    {
        $user = Auth::user();
        if (!$user)
            return ResponseFormatter::error('Unauthenticated', 401);

        $request->validate([
            'reason' => ['required', 'string', 'max:500'],
        ]);

        $allowed = method_exists($user, 'hasAnyRole')
            ? $user->hasAnyRole(['SPV', 'Supervisor', 'Admin'])
            : ($user->hasRole('SPV') || $user->hasRole('Supervisor') || $user->hasRole('Admin'));
        if (!$allowed)
            return ResponseFormatter::error('Forbidden', 403);

        $submission = Submission::with(['employee:id,name', 'branch'])->find($id);
        if (!$submission)
            return ResponseFormatter::error('Submission not found', 404);
        if ((int) $submission->status === SubmissionStatusEnum::Approved->value) {
            return ResponseFormatter::error('Already approved', 422);
        }
        if ((int) $submission->status === SubmissionStatusEnum::Rejected->value) {
            return ResponseFormatter::error('Already rejected', 422);
        }

        DB::transaction(function () use ($submission, $user, $request) {
            $submission->status = SubmissionStatusEnum::Rejected->value;
            $submission->rejected_by = $user->id;
            $submission->rejected_at = now();
            $submission->rejected_reason = $request->string('reason')->toString();
            $submission->approved_by = null;
            $submission->approved_at = null;
            $submission->save();
        });

        $submission->refresh();
        return ResponseFormatter::success(new SubmissionResource($submission), 'Rejected');
    }
}
