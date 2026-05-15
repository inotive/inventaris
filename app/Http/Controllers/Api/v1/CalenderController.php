<?php

namespace App\Http\Controllers\Api\v1;

use App\Helpers\ResponseFormatter;
use App\Http\Controllers\Controller;
use App\Http\Resources\Calendar\CalendarResource;
use App\Models\AttendanceShiftWork;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CalenderController extends Controller
{
    public function index(Request $request)
    {
        $bulanSekarang = $request->month ?? date('m');

        $attendanceShiftWorks = AttendanceShiftWork::with('shift')
            ->whereMonth('work_date', $bulanSekarang)
            ->where('employee_id', Auth::user()->employee->id)
            ->get();

        return ResponseFormatter::success(CalendarResource::collection($attendanceShiftWorks), 'Data shift kerja berhasil diambil');
    }
}
