<?php

namespace App\Http\Controllers\Api\v1;

use App\Enums\EmployeeLeaveApprovalStatusEnum;
use App\Enums\EmployeeLeaveRequestStatusEnum;
use App\Enums\LeaveTypeCategoryEnum;
use App\Helpers\FileHelper;
use App\Helpers\ResponseFormatter;
use App\Http\Controllers\Controller;
use App\Http\Resources\EmployeeLeaveApprovalResource;
use App\Http\Resources\EmployeeLeaveRequestResource;
use App\Models\Attendance;
use App\Models\EmployeeLeaveApproval;
use App\Models\EmployeeLeaveBalance;
use App\Models\EmployeeLeaveRequest;
use App\Models\AttendanceShiftWork;
use App\Services\NotificationService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class EmployeeLeaveRequestController extends Controller
{
    /**
     * Display a paginated list of employee leave requests with optional filters.
     */
    public function index(Request $request)
    {
        $limit = (int)$request->get('limit', 10);
        $offset = (int)$request->get('offset', 0);

        $query = EmployeeLeaveRequest::query();

        // Optional filters
        if ($request->filled('search')) {
            $query->where('reason', 'like', "%{$request->search}%");
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('employee_id')) {
            $query->where('employee_id', $request->employee_id);
        }

        if ($request->filled('approved_by')) {
            $query->where('approved_by', $request->approved_by);
        }

        if ($request->filled('start_date')) {
            $query->whereDate('start_date', '>=', $request->start_date);
        }

        if ($request->filled('end_date')) {
            $query->whereDate('end_date', '<=', $request->end_date);
        }

        $query->with('leaveType');

        $total = $query->count();

        $leaveRequests = $query
            ->orderByDesc('start_date')
            ->skip($offset)
            ->take($limit)
            ->get();

        return ResponseFormatter::successWithPaginationV2(
            EmployeeLeaveRequestResource::collection($leaveRequests),
            'List of leave requests retrieved successfully',
            $total,
            $leaveRequests->count(),
            $limit,
            (int)floor($offset / $limit) + 1,
            (int)ceil($total / $limit)
        );
    }

    /**
     * Create a new leave request for the authenticated employee.
     */
    public function store(Request $request)
    {
        $user = Auth::user();
        $employee = $user?->employee;

        if (!$employee) {
            return ResponseFormatter::error('Employee not found for authenticated user', 400);
        }
        Log::info('Employee leave request validation passed', [
            'employee_id' => $employee->id,
            'employee_name' => $employee->name,
            'user_id' => $user->id,
            'validated_data' => $request->all()
        ]);

        $validated = $request->validate([
            'leave_type_id' => 'required|exists:leave_types,id',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'reason' => 'nullable|string|max:255',
            'attachment' => 'nullable|array|max:5',
        ]);

        $start = Carbon::parse($validated['start_date']);
        $end = Carbon::parse($validated['end_date']);

        // Calculate total days based on scheduled work shifts
        $totalDays = $this->calculateTotalLeaveDays($employee->id, $start, $end);

        // Validation: check leave balance - hanya untuk cuti tahunan
        $year = $start->year;
        $leaveType = \App\Models\LeaveType::find($validated['leave_type_id']);

        if ($leaveType && $leaveType->category == LeaveTypeCategoryEnum::ANNUAL_LEAVE) {
            $leaveBalance = EmployeeLeaveBalance::where('employee_id', $employee->id)
                ->where('leave_type_id', $validated['leave_type_id'])
                ->where('year', $year)
                ->first();


            if (!$leaveBalance) {
                return ResponseFormatter::error('Saldo cuti tidak ditemukan untuk tahun ' . $year, 422);
            }

            if ($leaveBalance->remaining_quota < $totalDays) {
                return ResponseFormatter::error(
                    "Saldo cuti tidak mencukupi. Sisa saldo: {$leaveBalance->remaining_quota} hari, dibutuhkan: {$totalDays} hari.",
                    422
                );
            }
        }

        // Log the leave request creation
        Log::info('Leave request created', [
            'employee_id' => $employee->id,
            'employee_name' => $employee->name,
            'leave_type_id' => $validated['leave_type_id'],
            'start_date' => $validated['start_date'],
            'end_date' => $validated['end_date'],
            'total_days' => $totalDays,
            'reason' => $validated['reason'] ?? null,
        ]);

        // // Validation: prevent overlapping dates
        // $overlapExists = EmployeeLeaveRequest::where('employee_id', $employee->id)
        //     ->whereNotIn('status', ['rejected', 'cancelled'])
        //     ->where(function ($q) use ($start, $end) {
        //         $q->whereBetween('start_date', [$start, $end])
        //             ->orWhereBetween('end_date', [$start, $end])
        //             ->orWhere(function ($inner) use ($start, $end) {
        //                 $inner->where('start_date', '<=', $start)
        //                     ->where('end_date', '>=', $end);
        //             });
        //     })
        //     ->exists();

        // if ($overlapExists) {
        //     return ResponseFormatter::error('Anda sudah memiliki pengajuan cuti di rentang tanggal tersebut.', 422);
        // }

        // Validation: prevent leave in the same ISO week
        $startWeek = $start->isoWeek();

        // $sameWeekExists = EmployeeLeaveRequest::where('employee_id', $employee->id)
        //     ->whereYear('start_date', $year)
        //     ->whereNotIn('status', ['rejected', 'cancelled'])
        //     ->whereRaw('WEEK(start_date, 3) = ?', [$startWeek])
        //     ->exists();

        // if ($sameWeekExists) {
        //     return ResponseFormatter::error('Anda sudah memiliki pengajuan cuti di minggu yang sama.', 422);
        // }

        \Log::info('Creating leave request', [
            'employee_id' => $employee->id,
            'start_date' => $start->toDateString(),
            'end_date' => $end->toDateString(),
            'total_days' => $totalDays,
            'leave_type_id' => $validated['leave_type_id']
        ]);


        //cek apakah ada pengajuan cuti dihari yang sama
        // Cek apakah ada pengajuan cuti di dalam rentang tanggal ini (overlapping)
        $sameDayExists = EmployeeLeaveRequest::where('employee_id', $employee->id)
            ->whereNotIn('status', ['rejected', 'cancelled'])
            ->where(function ($q) use ($start, $end) {
                $q->where('start_date', '<=', $end)
                    ->where('end_date', '>=', $start);
            })
            ->exists();

        if ($sameDayExists) {
            return ResponseFormatter::error('Anda sudah memiliki pengajuan cuti di hari yang sama.', 422);
        }

        return DB::transaction(function () use ($validated, $employee, $user, $totalDays, $start, $end) {
            $attachments = !empty($validated['attachment'])
                ? FileHelper::saveBase64Files($validated['attachment'], 'leave_attachments')
                : [];

            $leaveRequest = EmployeeLeaveRequest::create([
                'employee_id' => $employee->id,
                'leave_type_id' => $validated['leave_type_id'],
                'start_date' => $start,
                'end_date' => $end,
                'total_days' => $totalDays,
                'reason' => $validated['reason'] ?? '-',
                'attachment' => $attachments,
                'status' => EmployeeLeaveRequestStatusEnum::PENDING,
            ]);

            \Log::info('Leave request created successfully', [
                'leave_request_id' => $leaveRequest->id,
                'employee_id' => $employee->id
            ]);

            DB::table('log_activies')->insert([
                'users_id' => $user->id,
                'model_type' => EmployeeLeaveRequest::class,
                'model_id' => $leaveRequest->id,
                'description' => "Mengajukan cuti #{$leaveRequest->id} ({$leaveRequest->reason})",
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            try {
                app(NotificationService::class)->notifyAdminsOnSubmission(
                    'leave',
                    $leaveRequest->id,
                    $user,
                    [
                        'start_date' => $start->format('d M Y'),
                        'end_date' => $end->format('d M Y'),
                        'total_days' => $totalDays,
                    ]
                );
                \Log::info('Notification sent for leave request', ['leave_request_id' => $leaveRequest->id]);
            } catch (\Throwable $e) {
                \Log::error('Failed to send notification for leave request', [
                    'leave_request_id' => $leaveRequest->id,
                    'error' => $e->getMessage()
                ]);
            }

            return ResponseFormatter::success(
                new EmployeeLeaveRequestResource($leaveRequest->load(['leaveType'])),
                'Leave request created successfully'
            );
        });
    }

    /**
     * Show the detail of a leave request by ID.
     */
    public function show($id)
    {
        $leaveRequest = EmployeeLeaveRequest::with(['employee', 'leaveType', 'approvals'])->find($id);

        if (!$leaveRequest) {
            return ResponseFormatter::error('Leave request not found', 404);
        }

        return ResponseFormatter::success(
            new EmployeeLeaveRequestResource($leaveRequest),
            'Leave request detail retrieved successfully'
        );
    }


    /**
     * Edit/update a leave request (only if pending).
     */
    public function update(Request $request, $id)
    {
        try {
            \Log::info("Update leave request", ['id' => $id, 'data' => $request->all()]);
            $leaveRequest = EmployeeLeaveRequest::find($id);

            if (!$leaveRequest) {
                return ResponseFormatter::error('Leave request not found', 404);
            }

            if ($leaveRequest->status !== EmployeeLeaveRequestStatusEnum::PENDING) {
                return ResponseFormatter::error('Only pending leave requests can be edited', 400);
            }

            $employee = Auth::user()?->employee ?? null;
            if (!$employee) {
                return ResponseFormatter::error('Employee not found for authenticated user', 400);
            }

            $validated = $request->validate([
                'leave_type_id' => 'sometimes|exists:leave_types,id',
                'start_date' => 'sometimes|date',
                'end_date' => 'sometimes|date|after_or_equal:start_date',
                'reason' => 'nullable|string|max:255',
                'attachment' => 'nullable|array|max:5',
            ]);

            // Recalculate total_days if date changed
            if (!empty($validated['start_date']) || !empty($validated['end_date'])) {
                $start = $validated['start_date'] ?? $leaveRequest->start_date;
                $end = $validated['end_date'] ?? $leaveRequest->end_date;
                $validated['total_days'] = Carbon::parse($start)->diffInDays(Carbon::parse($end)) + 1;

                // Cek apakah ada pengajuan cuti di dalam rentang tanggal ini (overlapping)
                $sameDayExists = EmployeeLeaveRequest::where('employee_id', $leaveRequest->employee_id)
                    ->whereNotIn('status', ['rejected', 'cancelled'])
                    ->where(function ($q) use ($start, $end) {
                        $q->where('start_date', '<=', $end)
                            ->where('end_date', '>=', $start);
                    })
                    ->where('employee_id', '!=', $leaveRequest->employee_id)
                    ->where('id', '!=', $leaveRequest->id)
                    ->exists();

                if ($sameDayExists) {
                    return ResponseFormatter::error('Anda sudah memiliki pengajuan cuti di hari yang sama.', 422);
                }
            }

            if (!empty($validated['attachment'])) {
                $validated['attachment'] = FileHelper::saveBase64Files($validated['attachment'], 'leave_attachments');
            }

            $leaveRequest->update($validated);

            return ResponseFormatter::success(
                new EmployeeLeaveRequestResource($leaveRequest->load(['leaveType'])),
                'Leave request updated successfully'
            );
        } catch (\Throwable $e) {
            \Log::error('Failed to update leave request', [
                'id' => $id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return ResponseFormatter::error('Failed to update leave request', 500);
        }
    }


    /**
     * Approve a leave request by approver.
     */
    public function approve(Request $request, $id)
    {
        try {

            $user = Auth::user();
            $employee = $user?->employee;

            // Pastikan user punya data karyawan
            if (!$employee) {
                return ResponseFormatter::error('Data karyawan tidak ditemukan', 400);
            }

            // Hanya Admin & Supervisor yang boleh approve
            if (!$user->hasAnyRole(['Superadmin', 'Admin', 'Supervisor'])) {
                return ResponseFormatter::error('Anda tidak memiliki izin untuk menyetujui permintaan cuti.', 403);
            }

            $leaveRequest = EmployeeLeaveRequest::find($id);
            if (!$leaveRequest) {
                return ResponseFormatter::error('Data tidak ditemukan', 404);
            }

            // Jika status cuti bukan pending, berarti sudah dikonfirmasi sebelumnya
            if ($leaveRequest->status !== EmployeeLeaveRequestStatusEnum::PENDING) {
                return ResponseFormatter::error('Permintaan ini sudah dikonfirmasi sebelumnya.', 422);
            }

            // Validasi tambahan untuk input note (optional)
            $validated = $request->validate([
                'note' => 'nullable|string|max:255',
            ]);

            DB::transaction(function () use ($validated, $employee, $leaveRequest, $user) {
                // Cek saldo cuti karyawan berdasarkan jenis cuti dan tahun
                $year = $leaveRequest->start_date->year;
                // Pastikan relasi leaveType sudah dimuat
                $leaveType = $leaveRequest->leaveType;

                // Hanya cek dan kurangi saldo jika cuti tahunan
                if ($leaveType->category->value == LeaveTypeCategoryEnum::ANNUAL_LEAVE->value) {
                    $leaveBalance = EmployeeLeaveBalance::where('employee_id', $leaveRequest->employee_id)
                        ->where('leave_type_id', $leaveRequest->leave_type_id)
                        ->where('year', $year)
                        ->lockForUpdate() // penting untuk mencegah race condition
                        ->first();

                    if (!$leaveBalance) {
                        throw new \Exception("Saldo cuti tidak ditemukan untuk tahun {$year}.");
                    }

                    if ($leaveBalance->remaining_quota < $leaveRequest->total_days) {
                        throw new \Exception('Sisa saldo cuti tidak mencukupi.');
                    }

                    // Kurangi saldo cuti
                    $leaveBalance->used_quota += $leaveRequest->total_days;
                    $leaveBalance->remaining_quota -= $leaveRequest->total_days;
                    $leaveBalance->save();
                }
                // Buat record approval baru karena belum ada sebelumnya
                EmployeeLeaveApproval::create([
                    'leave_request_id' => $leaveRequest->id,
                    'approved_id' => $employee->id,
                    'level' => 1,
                    'note' => $validated['note'] ?? '',
                    'status' => EmployeeLeaveApprovalStatusEnum::APPROVED,
                    'approved_at' => now(),
                ]);

                // Update status cuti menjadi approved
                $leaveRequest->update([
                    'status' => EmployeeLeaveRequestStatusEnum::APPROVED,
                    'admin_notes' => $validated['note'] ?? '',
                    'approved_by' => $employee->id,
                    'approved_at' => now(),
                ]);

                $dateWork = $leaveRequest->employee->attendanceShiftWorks()->where('work_date', '>=', $leaveRequest->start_date)->limit($leaveRequest->total_days)->pluck('work_date');
                foreach ($dateWork as $date) {
                    Attendance::create([
                        'employee_id' => $leaveRequest->employee_id,
                        'department_id' => $leaveRequest->employee->department_id,
                        'tanggal' => $date,
                        'status' =>  $leaveRequest->leave_type_id == 2 ? Attendance::STATUS_SICK : Attendance::STATUS_LEAVE,
                        'note' => $validated['note'] ?? '',
                    ]);
                }

                // Simpan ke log aktivitas
                DB::table('log_activies')->insert([
                    'users_id' => $user->id,
                    'model_type' => EmployeeLeaveRequest::class,
                    'model_id' => $leaveRequest->id,
                    'description' => "Menyetujui cuti #{$leaveRequest->id}",
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                // Kirim notifikasi ke karyawan
                try {
                    app(NotificationService::class)->notifyStaffOnFeedback(
                        'leave',
                        $leaveRequest->id,
                        $leaveRequest->employee->user->id,
                        'approved',
                        [
                            'start_date' => $leaveRequest->start_date->format('d M Y'),
                            'end_date' => $leaveRequest->end_date->format('d M Y'),
                            'total_days' => $leaveRequest->total_days,
                        ]
                    );
                } catch (\Throwable $e) {
                    report($e);
                }
            });

            return ResponseFormatter::success(
                new EmployeeLeaveRequestResource($leaveRequest->fresh(['approvals', 'leaveType'])),
                'Leave request approved successfully'
            );
        } catch (\Exception $e) {

            return ResponseFormatter::error($e->getMessage(), is_int($e->getCode()) && $e->getCode() > 0 ? $e->getCode() : 422);
        }
    }


    /**
     * Cancel a leave request (only if pending).
     */
    public function cancel($id)
    {
        $leaveRequest = EmployeeLeaveRequest::find($id);

        if (!$leaveRequest) {
            return ResponseFormatter::error('Leave request not found', 404);
        }

        if ($leaveRequest->status !== EmployeeLeaveRequestStatusEnum::PENDING) {
            return ResponseFormatter::error('Only pending leave requests can be cancelled', 400);
        }

        if (Auth::user()->employee->id !== $leaveRequest->employee_id) {
            return ResponseFormatter::error('Anda tidak memiliki izin untuk membatalkan permintaan cuti ini.', 403);
        }

        $leaveRequest->update([
            'status' => EmployeeLeaveRequestStatusEnum::CANCELLED,
            'approved_by' => Auth::user()->employee->id,
            'approved_at' => now(),
        ]);

        return ResponseFormatter::success(
            new EmployeeLeaveRequestResource($leaveRequest->load(['employee', 'leaveType', 'approvals'])),
            'Leave request cancelled successfully'
        );
    }

    /**
     * Reject a leave request by approver.
     */
    public function reject(Request $request, $id)
    {
        $user = Auth::user();
        $employee = $user?->employee;

        if (!$employee) {
            return ResponseFormatter::error('Data karyawan tidak ditemukan', 400);
        }

        if (!$user->hasAnyRole(['Superadmin', 'Admin', 'Supervisor'])) {
            return ResponseFormatter::error('Anda tidak memiliki izin untuk menolak permintaan cuti.', 403);
        }

        $leaveRequest = EmployeeLeaveRequest::find($id);
        if (!$leaveRequest) {
            return ResponseFormatter::error('Data tidak ditemukan', 404);
        }

        if ($leaveRequest->status !== EmployeeLeaveRequestStatusEnum::PENDING) {
            return ResponseFormatter::error('Permintaan ini sudah dikonfirmasi sebelumnya.', 422);
        }

        // Validasi tambahan untuk input note (optional)
        $validated = $request->validate([
            'note' => 'nullable|string|max:255',
        ]);

        DB::transaction(function () use ($validated, $employee, $leaveRequest, $user, $request) {
            // Buat record approval baru (karena belum ada sebelumnya)
            EmployeeLeaveApproval::create([
                'leave_request_id' => $leaveRequest->id,
                'approved_id' => $employee->id,
                'level' => 1,
                'status' => EmployeeLeaveApprovalStatusEnum::REJECTED,
                'note' => $validated['note'] ?? '',
                'approved_at' => now(),
            ]);

            // Update status cuti menjadi rejected
            $leaveRequest->update([
                'status' => EmployeeLeaveRequestStatusEnum::REJECTED,
                'admin_notes' => $validated['note'] ?? '',
                'approved_by' => $employee->id,
                'approved_at' => now(),
            ]);

            // Simpan ke log aktivitas
            DB::table('log_activies')->insert([
                'users_id' => $user->id,
                'model_type' => EmployeeLeaveRequest::class,
                'model_id' => $leaveRequest->id,
                'description' => "Menolak cuti #{$leaveRequest->id}",
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // Kirim notifikasi ke karyawan
            try {
                app(NotificationService::class)->notifyStaffOnFeedback(
                    'leave',
                    $leaveRequest->id,
                    $leaveRequest->employee->user->id,
                    'rejected',
                    [
                        'reason' => $request->reason ?? '-',
                        'start_date' => $leaveRequest->start_date->format('d M Y'),
                        'end_date' => $leaveRequest->end_date->format('d M Y'),
                        'total_days' => $leaveRequest->total_days,
                    ]
                );
            } catch (\Throwable $e) {
                report($e);
            }
        });

        return ResponseFormatter::success(
            new EmployeeLeaveRequestResource($leaveRequest->fresh(['approvals', 'leaveType'])),
            'Leave request rejected successfully'
        );
    }

    /**
     * Helper method to calculate total leave days based on scheduled work shifts
     *
     * @param int $employeeId
     * @param Carbon $start
     * @param Carbon $end
     * @return int
     */
    private function calculateTotalLeaveDays($employeeId, $start, $end)
    {
        // Count distinct scheduled working dates for the employee within the range
        return AttendanceShiftWork::query()
            ->where('employee_id', $employeeId)
            ->whereBetween('work_date', [$start->toDateString(), $end->toDateString()])
            ->distinct()
            ->count('work_date');
    }

    /**
     * Calculate total leave days to be used based on scheduled shifts for an employee
     * within a given date range [start_date, end_date].
     *
     * Request body:
     * - start_date (Y-m-d, required)
     * - end_date (Y-m-d, required, >= start_date)
     */
    public function calculateDays(Request $request)
    {
        $validated = $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
        ]);

        $employeeId = optional(Auth::user())->employee?->id;
        if (!$employeeId) {
            return ResponseFormatter::error('Employee not found for authenticated user', 400);
        }

        $start = Carbon::parse($validated['start_date'])->startOfDay();
        $end = Carbon::parse($validated['end_date'])->endOfDay();

        // Use helper method to calculate total days
        $total = $this->calculateTotalLeaveDays($employeeId, $start, $end);

        return ResponseFormatter::success([
            'employee_id' => $employeeId,
            'start_date' => $start->toDateString(),
            'end_date' => $end->toDateString(),
            'total_days' => $total,
            'formula' => 'Count distinct work_date in attendance_shift_works between start and end for the employee',
        ], 'Total leave days calculated');
    }
}
