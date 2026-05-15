<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Models\EmployeeLeaveBalance;
use App\Models\EmployeeLeaveRequest;
use App\Enums\EmployeeLeaveRequestStatusEnum;
use App\Enums\LeaveTypeCategoryEnum;
use Illuminate\Http\Request;

class EmployeeLeaveBalanceController extends Controller
{
    /**
     * Get leave balance based on filters : leave_type_id, employee_id, year, (optional) category
     */
    public function getLeaveBalance(Request $request): \Illuminate\Http\JsonResponse
    {
        $user = \Illuminate\Support\Facades\Auth::user();
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated',
            ], 401);
        }

        $employeeId = optional($user->employee)->id;
        if (!$employeeId) {
            return response()->json([
                'success' => false,
                'message' => 'No employee profile found',
            ], 422);
        }

        // Validasi parameter — year opsional, default tahun berjalan
         $validated = $request->validate([
            'year'        => 'nullable|integer|min:2000|max:2100',
            'category'    => 'nullable|string', // optional filter
        ]);

        $year       =now()->year;
     $category   = $validated['category'] ?? null;

      $query = EmployeeLeaveBalance::with('leaveType')
    ->where('employee_id', $employeeId)
    ->where('year', $year)
    ->where('leave_type_id', 1);

        $leaves = $query->get();

        if ($leaves->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'Data cuti tidak ditemukan',
                'data'    => null,
            ]);
        }

        // Hitung cuti terpakai dari leave request yang berstatus "approved" di tahun berjalan

        $totalQuota     = $leaves->sum('total_quota');
        $usedQuota      = $leaves->sum('used_quota');
        $remainingQuota = $leaves->sum('remaining_quota');

        return response()->json([
            'success' => true,
            'message' => 'Data cuti ditemukan',
            'data'    => [
                'employee_id'     => (int) $employeeId,
                'year'            => (int) $year,
                'total_quota'     => $totalQuota ?? 0,
                'used_quota'      => $usedQuota ?? 0,
                'remaining_quota' => $remainingQuota ?? 0,
            ],
        ]);
    }
}
