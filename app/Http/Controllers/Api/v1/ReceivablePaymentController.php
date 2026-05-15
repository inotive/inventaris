<?php

namespace App\Http\Controllers\Api\v1;

use App\Helpers\ResponseFormatter;
use App\Http\Controllers\Controller;
use App\Models\EmployeeReceivableBalance;
use App\Models\Receivable;
use App\Models\ReceivablePayment;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class ReceivablePaymentController extends Controller
{
    /**
     * Get payment history for a receivable
     */
    public function index($receivableId)
    {
        try {
            // Check authentication using web guard
            if (!Auth::guard('web')->check()) {
                return ResponseFormatter::error('Unauthenticated', 401);
            }

            $receivable = Receivable::find($receivableId);

            if (!$receivable) {
                return ResponseFormatter::error('Receivable not found', 404);
            }

            // Check if user has permission to view this receivable
            // Admin can view all, employee can only view their own
            $user = Auth::guard('web')->user();
            $employee = $user?->employee ?? null;
            if (!$employee) {
                return ResponseFormatter::error('Employee not found for authenticated user', 400);
            }

            // Check if user is admin or owns the receivable
            $isAdmin = $user->hasRole('Superadmin', 'web') || $user->hasRole('Admin', 'web');
            if (!$isAdmin && $receivable->request_by !== $employee->id) {
                return ResponseFormatter::error('Unauthorized to view this receivable', 403);
            }

            $payments = ReceivablePayment::where('receivable_id', $receivableId)
                ->orderBy('paid_date', 'desc')
                ->orderBy('created_at', 'desc')
                ->get();

            $formattedPayments = $payments->map(function ($payment) {
                return [
                    'id' => $payment->id,
                    'receivable_id' => $payment->receivable_id,
                    'paid_date' => $payment->paid_date,
                    'amount' => (float) $payment->amount,
                    'created_at' => $payment->created_at,
                    'updated_at' => $payment->updated_at,
                ];
            });

            return ResponseFormatter::success($formattedPayments, 'Payment history retrieved successfully');
        } catch (\Exception $e) {
            return ResponseFormatter::error('Failed to retrieve payment history: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Store a new payment for a receivable
     */
    public function store(Request $request, $receivableId)
    {
        try {
            // Check authentication using web guard
            if (!Auth::guard('web')->check()) {
                return ResponseFormatter::error('Unauthenticated', 401);
            }

            $receivable = Receivable::find($receivableId);

            if (!$receivable) {
                return ResponseFormatter::error('Receivable not found', 404);
            }

            // Check if user has permission to add payment
            $user = Auth::guard('web')->user();
            $employee = $user?->employee ?? null;
            if (!$employee) {
                return ResponseFormatter::error('Employee not found for authenticated user', 400);
            }

            // Check if user is admin
            $isAdmin = $user->hasRole('Superadmin', 'web') || $user->hasRole('Admin', 'web');
            if (!$isAdmin) {
                return ResponseFormatter::error('Unauthorized to add payment', 403);
            }

            // Validate request
            $validator = Validator::make($request->all(), [
                'paid_date' => 'required|date',
                'amount' => 'required|numeric|min:1',
            ], [
                'paid_date.required' => 'Tanggal pembayaran wajib diisi',
                'paid_date.date' => 'Tanggal pembayaran harus berupa tanggal yang valid',
                'amount.required' => 'Jumlah pembayaran wajib diisi',
                'amount.numeric' => 'Jumlah pembayaran harus berupa angka',
                'amount.min' => 'Jumlah pembayaran harus lebih dari 0',
            ]);

            if ($validator->fails()) {
                return ResponseFormatter::error('Validation failed', 422, $validator->errors());
            }

            // Calculate total paid amount
            $totalPaid = ReceivablePayment::where('receivable_id', $receivableId)
                ->sum('amount');

            $remainingDebt = $receivable->amount - $totalPaid;

            // Check if payment amount exceeds remaining debt
            if ($request->amount > $remainingDebt) {
                return ResponseFormatter::error(
                    'Jumlah pembayaran tidak boleh melebihi sisa piutang (Sisa: ' . number_format($remainingDebt, 0, ',', '.') . ')',
                    422
                );
            }

            // Start database transaction
            DB::beginTransaction();

            try {
                // Create payment
                $payment = ReceivablePayment::create([
                    'receivable_id' => $receivableId,
                    'paid_date' => $request->paid_date,
                    'amount' => $request->amount,
                ]);

                // Update EmployeeReceivableBalance
                $this->updateEmployeeReceivableBalance(
                    $receivable->request_by,
                    $request->paid_date,
                    $request->amount,
                    'decrease' // Decrease used_amount because payment reduces the used receivable
                );

                // Commit transaction
                DB::commit();

                $formattedPayment = [
                    'id' => $payment->id,
                    'receivable_id' => $payment->receivable_id,
                    'paid_date' => $payment->paid_date,
                    'amount' => (float) $payment->amount,
                    'created_at' => $payment->created_at,
                    'updated_at' => $payment->updated_at,
                ];

                return ResponseFormatter::success($formattedPayment, 'Payment added successfully');
            } catch (\Exception $e) {
                // Rollback transaction on error
                DB::rollBack();
                throw $e;
            }
        } catch (\Exception $e) {
            return ResponseFormatter::error('Failed to add payment: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Delete a payment
     */
    public function destroy($paymentId)
    {
        try {
            // Check authentication using web guard
            if (!Auth::guard('web')->check()) {
                return ResponseFormatter::error('Unauthenticated', 401);
            }

            $payment = ReceivablePayment::with('receivable')->find($paymentId);

            if (!$payment) {
                return ResponseFormatter::error('Payment not found', 404);
            }

            // Check if user has permission to delete payment
            $user = Auth::guard('web')->user();
            $employee = $user?->employee ?? null;
            if (!$employee) {
                return ResponseFormatter::error('Employee not found for authenticated user', 400);
            }

            // Check if user is admin
            $isAdmin = $user->hasRole('Superadmin', 'web') || $user->hasRole('Admin', 'web');
            if (!$isAdmin) {
                return ResponseFormatter::error('Unauthorized to delete payment', 403);
            }

            // Store payment data before deletion
            $employeeId = $payment->receivable->request_by;
            $paidDate = $payment->paid_date;
            $amount = $payment->amount;

            // Start database transaction
            DB::beginTransaction();

            try {
                // Delete payment
                $payment->delete();

                // Update EmployeeReceivableBalance
                $this->updateEmployeeReceivableBalance(
                    $employeeId,
                    $paidDate,
                    $amount,
                    'increase' // Increase used_amount because payment deletion increases the used receivable
                );

                // Commit transaction
                DB::commit();

                return ResponseFormatter::success(null, 'Payment deleted successfully');
            } catch (\Exception $e) {
                // Rollback transaction on error
                DB::rollBack();
                throw $e;
            }
        } catch (\Exception $e) {
            return ResponseFormatter::error('Failed to delete payment: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Update EmployeeReceivableBalance when payment is added or deleted
     *
     * @param int $employeeId
     * @param string $paidDate
     * @param float $amount
     * @param string $operation 'increase' or 'decrease'
     * @return void
     */
    private function updateEmployeeReceivableBalance($employeeId, $paidDate, $amount, $operation = 'decrease')
    {
        // Get period from paid_date
        $date = Carbon::parse($paidDate);
        $periodYear = $date->year;
        $periodMonth = $date->month;

        // Find or create EmployeeReceivableBalance for this period
        $balance = EmployeeReceivableBalance::where('employee_id', $employeeId)
            ->where('period_year', $periodYear)
            ->where('period_month', $periodMonth)
            ->first();

        if (!$balance) {
            // If balance doesn't exist, we can't update it
            // This might happen if the receivable was created before the balance system
            // In this case, we'll skip the update
            return;
        }

        // Update based on operation
        if ($operation === 'decrease') {
            // Payment reduces used_amount (piutang sudah dibayar)
            // used_amount berkurang karena piutang yang digunakan berkurang
            $balance->used_amount = max(0, $balance->used_amount - $amount);
        } else {
            // Payment deletion increases used_amount (piutang kembali)
            // used_amount bertambah karena piutang yang digunakan bertambah
            $balance->used_amount = $balance->used_amount + $amount;
        }

        // Always recalculate remaining_amount to ensure consistency
        $balance->remaining_amount = $balance->limit_amount - $balance->used_amount;

        $balance->save();
    }
}

