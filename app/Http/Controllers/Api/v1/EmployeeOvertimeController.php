<?php

namespace App\Http\Controllers\Api\v1;

use App\Enums\EmployeeLeaveRequestStatusEnum;
use App\Enums\EmployeeOvertimeStatusEnum;
use App\Helpers\ResponseFormatter;
use App\Http\Controllers\Controller;
use App\Http\Resources\EmployeeOvertimeResource;
use App\Models\EmployeeOvertime;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class EmployeeOvertimeController extends Controller
{
    public function index(Request $request)
    {
        $limit = (int)$request->get('limit', 10);
        $offset = (int)$request->get('offset', 0);

        $query = EmployeeOvertime::with(['employee', 'approved']);

        if ($request->filled('employee_id')) {
            $query->where('employee_id', $request->employee_id);
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $user = Auth::user();
        $isSuperAdmin = method_exists($user, 'hasRole') && $user->hasRole('Superadmin');

        if (!$isSuperAdmin && $user->employee && $user->employee->branch_id && $user->employee->branch_id != 2) {
            $branchId = $user->employee->branch_id;
            $query->whereHas('employee', function ($q) use ($branchId) {
                $q->where('branch_id', $branchId);
            });
        }

        $total = $query->count();
        $overtimes = $query->offset($offset)->limit($limit)->get();

        return ResponseFormatter::successWithPaginationV2(
            EmployeeOvertimeResource::collection($overtimes),
            'List of overtimes retrieved successfully',
            $total,
            $overtimes->count(),
            $limit,
            (int)floor($offset / $limit) + 1,
            (int)ceil($total / $limit)
        );
    }

    // Return overtimes for the authenticated user's employee
    public function history(Request $request)
    {
        $employee = Auth::user()?->employee ?? null;
        if (!$employee) return \App\Helpers\ResponseFormatter::error('Employee not found for authenticated user', 400);

        $query = EmployeeOvertime::with(['employee.branch', 'approved'])
            ->where('employee_id', $employee->id);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('month')) {
            $month = (int) $request->month;
            if ($month >= 1 && $month <= 12) {
                $query->whereMonth('date', $month);
            }
        }

        // Build summary counters before pagination
        $baseCounter = (clone $query);
        $total = (clone $baseCounter)->count();
        $approved = (clone $baseCounter)->where('status', EmployeeOvertimeStatusEnum::APPROVED)->count();
        $pending = (clone $baseCounter)->where('status', EmployeeOvertimeStatusEnum::PENDING)->count();
        $rejected = (clone $baseCounter)->where('status', EmployeeOvertimeStatusEnum::REJECTED)->count();

        $rows = $query->orderByDesc('date')->get();

        // Normalize for mobile list
        $items = $rows->map(function ($ot) {
            $durationMin = (int) ($ot->duration_hours ?? 0);
            return [
                'id' => $ot->id,
                'date' => (string) $ot->date,
                'start_time' => $ot->start_time,
                'end_time' => $ot->end_time,
                'duration_minutes' => $durationMin,
                'status' => $ot->status,
                'reason' => $ot->reason,
            ];
        });

        return \App\Helpers\ResponseFormatter::success([
            'summary' => [
                'total' => $total,
                'approved' => $approved,
                'pending' => $pending,
                'rejected' => $rejected,
            ],
            'items' => $items,
        ], 'Overtime history retrieved');
    }

    public function show($id)
    {
        $overtime = EmployeeOvertime::with(['employee', 'approved'])->findOrFail($id);

        return ResponseFormatter::success(
            new EmployeeOvertimeResource($overtime),
            'Overtime retrieved successfully'
        );
    }

    public function store(Request $request)
    {
        $employee = Auth::user()?->employee ?? null;
        if (!$employee) {
            return ResponseFormatter::error('Employee not found for authenticated user', 400);
        }

        $validated = $request->validate([
            'date' => 'required|date',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
            'reason' => 'nullable|string',
        ]);

        $date = $validated['date'];
        $start = $validated['start_time'];
        $end = $validated['end_time'];

        $duration = $this->durationDiffInMinutes($date, $start, $end);

        $overtime = EmployeeOvertime::create([
            'employee_id' => $employee->id,
            'date' => $validated['date'],
            'start_time' => $validated['start_time'],
            'end_time' => $validated['end_time'],
            'duration_hours' => $duration,
            'reason' => $validated['reason'] ?? null,
            'status' => EmployeeOvertimeStatusEnum::PENDING,
        ]);

        // Send push notification to admin/SPV
        try {
            $notificationService = app(\App\Services\NotificationService::class);
            $notificationService->notifyAdminsOnSubmission(
                'overtime',
                $overtime->id,
                Auth::user(),
                [
                    'date' => $validated['date'],
                    'start_time' => $validated['start_time'],
                    'end_time' => $validated['end_time'],
                    'duration' => $duration
                ]
            );
        } catch (\Throwable $e) {
            // dd($e);
            // Silent fail – notification should not block API
        }

        return ResponseFormatter::success(
            new EmployeeOvertimeResource($overtime),
            'Overtime created successfully',
            201
        );
    }

    public function update(Request $request, $id): \Illuminate\Http\JsonResponse
    {
        $overtime = EmployeeOvertime::findOrFail($id);

        if ($overtime->status !== EmployeeOvertimeStatusEnum::PENDING) {
            return ResponseFormatter::error('Only pending overtime can be edited', 400);
        }

        $employee = Auth::user()?->employee ?? null;
        if (!$employee) {
            return ResponseFormatter::error('Employee not found for authenticated user', 400);
        }

        $validated = $request->validate([
            'start_time' => 'sometimes|date_format:H:i',
            'end_time' => 'sometimes|date_format:H:i|after:start_time',
            'reason' => 'nullable|string',
            'notes' => 'nullable|string',
        ]);

        $date = Carbon::parse($validated['date'] ?? $overtime->date)->format('Y-m-d');;
        $start = $validated['start_time'] ?? $overtime->start_time;
        $end = $validated['end_time'] ?? $overtime->end_time;

        $duration = $this->durationDiffInMinutes($date, $start, $end);

        $updateData = [
            'date' => $date,
            'start_time' => $start,
            'end_time' => $end,
            'duration_hours' => $duration,
            'reason' => $validated['reason'] ?? $overtime->reason,
            'status' => $overtime->status,
        ];

        $overtime->update($updateData);

        return ResponseFormatter::success(
            new EmployeeOvertimeResource($overtime),
            'Overtime updated successfully'
        );
    }

    public function destroy($id)
    {
        $overtime = EmployeeOvertime::findOrFail($id);
        $overtime->delete();

        return ResponseFormatter::success(null, 'Overtime deleted successfully');
    }

    public function changeStatus(Request $request, $id)
    {
        $overtime = EmployeeOvertime::findOrFail($id);

        if ($overtime->status !== EmployeeOvertimeStatusEnum::PENDING) {
            return ResponseFormatter::error('Only pending overtime can confirmation', 400);
        }


        $employee = Auth::user()?->employee ?? null;
        if (!$employee) {
            return ResponseFormatter::error('Employee not found for authenticated user', 400);
        }

        $validated = $request->validate([
            'status' => ['required', Rule::in([
                EmployeeOvertimeStatusEnum::APPROVED->value,
                EmployeeOvertimeStatusEnum::REJECTED->value,
            ])],
            'notes' => 'nullable|string',
        ]);

        $overtime->update([
            'status' => $validated['status'],
            'notes' => $validated['notes'] ?? $overtime->notes,
            'approved_by' => $employee->id,
        ]);

        // Send notification to staff about status change
        try {
            $notificationService = app(\App\Services\NotificationService::class);
            $notificationService->notifyStaffOnFeedback(
                'overtime',
                $overtime->id,
                $overtime->employee->user_id,
                $validated['status'],
                ['note' => $validated['notes'] ?? '']
            );
        } catch (\Throwable $e) {
            // Silent fail – notification should not block API
        }

        return ResponseFormatter::success(
            new EmployeeOvertimeResource($overtime),
            'Overtime status updated successfully'
        );
    }

    public function durationDiffInMinutes($date, $startTime, $endTime): int
    {
        $startCarbon = Carbon::parse($date . ' ' . $startTime);
        $endCarbon = Carbon::parse($date . ' ' . $endTime);

        if ($endCarbon->lessThan($startCarbon)) {
            $endCarbon->addDay();
        }

        return (int)$startCarbon->diffInMinutes($endCarbon);
    }

    public function cancel(Request $request, $id)
    {
        $overtime = EmployeeOvertime::findOrFail($id);

        if ($overtime->status !== EmployeeOvertimeStatusEnum::PENDING) {
            return ResponseFormatter::error('Only pending overtime can be canceled', 400);
        }

        if ($overtime->employee_id != Auth::user()->employee->id) {
            return ResponseFormatter::error('You are not allowed to cancel this overtime', 403);
        }

        $overtime->update([
            'status' => 'cancelled',
            'approved_by' => Auth::user()->id,
            'approved_at' => now(),
        ]);

        //notifikasi
        $notificationService = app(\App\Services\NotificationService::class);
        $notificationService->notifyStaffOnFeedback(
            'overtime',
            $overtime->id,
            $overtime->employee->user_id,
            'cancelled',
            ['note' => $request->string('note')->toString()]
        );

        return ResponseFormatter::success(null, 'Overtime canceled successfully');
    }
}
