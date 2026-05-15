<?php

namespace App\Http\Controllers\Api\v1;

use App\Helpers\ResponseFormatter;
use App\Http\Controllers\Controller;
use App\Models\AttendanceShiftWork;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ShiftWorkController extends Controller
{
    /**
     * GET /api/v1/shift-works
     * Return authenticated user's shift assignments for a month or date range.
     * Query params:
     * - month: YYYY-MM (priority)
     * - date_from, date_to: YYYY-MM-DD
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        if (!$user) return ResponseFormatter::error('Unauthenticated', 401);
        $employeeId = optional($user->employee)->id;
        if (!$employeeId) return ResponseFormatter::error('User has no employee profile', 422);

        $month = $request->string('month')->toString();
        $dateFrom = $request->input('date_from');
        $dateTo = $request->input('date_to');

        $query = AttendanceShiftWork::query()
            ->with(['shift:id,code,name,start_time,end_time,late_tolerance'])
            ->where('employee_id', $employeeId)
            ->orderBy('work_date');

        if ($month) {
            try {
                $start = Carbon::createFromFormat('Y-m', $month)->startOfMonth();
                $end = (clone $start)->endOfMonth();
                $query->whereBetween('work_date', [$start->toDateString(), $end->toDateString()]);
            } catch (\Throwable $e) {
                // ignore invalid month; fall back to today month
                $start = now()->startOfMonth();
                $end = now()->endOfMonth();
                $query->whereBetween('work_date', [$start->toDateString(), $end->toDateString()]);
            }
        } elseif ($dateFrom || $dateTo) {
            if ($dateFrom && $dateTo) $query->whereBetween('work_date', [$dateFrom, $dateTo]);
            elseif ($dateFrom) $query->whereDate('work_date', '>=', $dateFrom);
            elseif ($dateTo) $query->whereDate('work_date', '<=', $dateTo);
        } else {
            // default: current month
            $start = now()->startOfMonth();
            $end = now()->endOfMonth();
            $query->whereBetween('work_date', [$start->toDateString(), $end->toDateString()]);
        }

        $rows = $query->get(['id','work_date','shift_id']);

        $data = $rows->map(function ($r) {
            $start = optional($r->shift)->start_time;
            $end = optional($r->shift)->end_time;
            $durationMinutes = null;
            if ($start && $end) {
                try {
                    $s = Carbon::createFromFormat('H:i:s', $start);
                    $e = Carbon::createFromFormat('H:i:s', $end);
                    if ($e->lessThanOrEqualTo($s)) {
                        $e->addDay(); // cross midnight
                    }
                    $durationMinutes = $e->diffInMinutes($s);
                } catch (\Throwable $e) {
                    $durationMinutes = null;
                }
            }
            return [
                'id' => $r->id,
                'date' => $r->work_date,
                'shift' => $r->shift ? [
                    'id' => $r->shift->id,
                    'code' => $r->shift->code,
                    'name' => $r->shift->name,
                    'start_time' => $r->shift->start_time,
                    'end_time' => $r->shift->end_time,
                    'duration_minutes' => $durationMinutes,
                ] : null,
            ];
        });

        return ResponseFormatter::success($data);
    }
}
