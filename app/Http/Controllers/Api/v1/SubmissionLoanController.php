<?php

namespace App\Http\Controllers\Api\v1;

use App\Actions\Data\Submission\Loan\CreateLoanRequest;
use App\Actions\Data\Submission\Loan\UpdateLoanRequest;
use App\Http\Controllers\Controller;
use App\Helpers\ResponseFormatter;
use App\Http\Requests\Submission\LoanCreateRequest as SubmissionLoanCreateRequest;
use App\Http\Requests\Submission\LoanUpdateRequest as SubmissionLoanUpdateRequest;
use App\Models\EmployeeReceivableBalance;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\Models\Receivable;
use App\Models\User;

class SubmissionLoanController extends Controller
{
    /**
     * Get loan detail
     *
     * @param int $kodeLoan
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($kodeLoan)
    {
        $loan = Receivable::with([
            'employee:id,name,branch_id,department_id',
            'employee.branch:id,name',
            'employee.department:id,name',
            'approver:id,name'
        ])->find($kodeLoan);

        if (!$loan) {
            return ResponseFormatter::error('Loan submission not found', 404);
        }

        $data = [
            'id' => $loan->id,
            'amount' => (float) $loan->amount,
            'date' => $loan->date,
            'tenor' => (int) $loan->tenor,
            'note' => $loan->note,
            'admin_notes' => $loan->admin_notes ?? null,
            'file_path' => $loan->file_path,
            'status' => $loan->status,
            'request_by' => [
                'id' => $loan->employee->id,
                'name' => $loan->employee->name,
                'branch' => $loan->employee->branch->name ?? null,
                'department' => $loan->employee->department->name ?? null,
            ],
            'approved_by' => $loan->approved_by ? [
                'id' => $loan->approver->id ?? null,
                'name' => $loan->approver->name ?? null,
            ] : null,
            'approved_at' => $loan->approved_at,
            'created_at' => $loan->created_at,
            'updated_at' => $loan->updated_at,
        ];

        return ResponseFormatter::success($data, 'Loan submission detail');
    }

    /**
     * Create loan request
     *
     * @param SubmissionLoanCreateRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function create(SubmissionLoanCreateRequest $request)
    {
        $data = app(CreateLoanRequest::class)->execute(Auth::user(), $request->validated());
        return ResponseFormatter::success(null, 'Data submission loan request berhasil dibuat');
    }

    /**
     * Update loan request
     *
     * @param SubmissionLoanUpdateRequest $request
     * @param string $kodeLoan
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(SubmissionLoanUpdateRequest $request, $kodeLoan)
    {
        $data = app(UpdateLoanRequest::class)->execute($request->validated(), $kodeLoan);
        return ResponseFormatter::success(null, 'Data submission loan request berhasil diubah');
    }

    /**
     * Approve loan submission
     */
    public function approve(Request $request, $kodeLoan)
    {
        $user = Auth::user();

        $receivable = Receivable::findOrFail($kodeLoan);

        if ($receivable->status == 'approved') {
            return ResponseFormatter::error('Pengajuan pinjaman sudah disetujui sebelumnya.', 400);
        }

        $employeeReceivableBalance = EmployeeReceivableBalance::where('employee_id', $receivable->request_by)
            ->where('period_year', now()->year)
            ->first();

        if ($employeeReceivableBalance && $employeeReceivableBalance->remaining_amount < $receivable->amount) {
            return ResponseFormatter::error('Sisa saldo pinjaman tidak mencukupi.', 400);
        }

        if ($employeeReceivableBalance) {
            $employeeReceivableBalance->update([
                'used_amount' => $employeeReceivableBalance->used_amount + $receivable->amount,
                'remaining_amount' => $employeeReceivableBalance->remaining_amount - $receivable->amount,
            ]);
        } else {
            $limitAmount = $receivable->employee->receivableBalance->loan_quota;
            EmployeeReceivableBalance::create([
                'employee_id' => $receivable->request_by,
                'period_year' => now()->year,
                'period_month' => now()->month,
                'limit_amount' => $limitAmount,
                'used_amount' => $receivable->amount,
                'remaining_amount' => $limitAmount - $receivable->amount,
            ]);
        }

        $receivable->update([
            'status' => 'approved',
            'approved_by' => $user->id,
            'approved_at' => now(),
        ]);

        //notifikasi
        $notificationService = app(\App\Services\NotificationService::class);
        $notificationService->notifyStaffOnFeedback(
            'loan',
            $receivable->id,
            $receivable->request_by,
            'approved',
            ['note' => $request->string('note')->toString()]
        );

        return ResponseFormatter::success(null, 'Pengajuan pinjaman berhasil disetujui.');
    }

    /**
     * Reject loan submission
     */
    public function reject(Request $request, $kodeLoan)
    {
        $user = Auth::user();

        $loan = Receivable::findOrFail($kodeLoan);

        $loan->update([
            'status' => 'rejected',
            'admin_notes' => $request->string('admin_notes')->toString() ?? '-',
            'approved_by' => $user->id,
            'approved_at' => now(),
        ]);

        // Send notification to staff about rejection
        $notificationService = app(\App\Services\NotificationService::class);
        $notificationService->notifyStaffOnFeedback(
            'loan',
            $loan->id,
            $loan->request_by,
            'rejected',
            ['note' => $request->string('note')->toString()]
        );

        return ResponseFormatter::success(null, 'Submission loan rejected');
    }

    /**
     * Cancel loan submission
     */
    public function cancel(Request $request, $kodeLoan)
    {
        $user = Auth::user();

        $receivable = Receivable::findOrFail($kodeLoan);

        if ($receivable->request_by != $user->employee->id) {
            return ResponseFormatter::error('Anda tidak memiliki izin untuk membatalkan permintaan pinjaman ini.', 403);
        }

        if ($receivable->status == 'canceled') {
            return ResponseFormatter::error('Pengajuan pinjaman sudah dibatalkan sebelumnya.', 400);
        }

        if ($receivable->status == 'approved') {
            return ResponseFormatter::error('Pengajuan pinjaman sudah disetujui sebelumnya.', 400);
        }

        $receivable->update([
            'status' => 'cancelled',
            'approved_by' => $user->id,
            'approved_at' => now(),
        ]);

        return ResponseFormatter::success(null, 'Pengajuan pinjaman berhasil dibatalkan.');

        //notifikasi
        $notificationService = app(\App\Services\NotificationService::class);
        $notificationService->notifyStaffOnFeedback(
            'loan',
            $receivable->id,
            $receivable->request_by,
            'cancelled',
            ['note' => $request->string('note')->toString()]
        );
    }
}
