<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use Illuminate\Http\Request;
use App\Http\Resources\AttendanceResource;
use Illuminate\Support\Facades\Auth;
use App\Models\AttendanceShiftWork;
use Illuminate\Support\Facades\Storage;

class AttendanceController extends Controller
{

    public function index(Request $request)
    {
        $user = Auth::user();
        $employee = $user ? $user->employee : null;
        $employeeId = $employee ? $employee->id : null;
        if (!$employeeId) {
            return response()->json(['message' => 'Employee not found'], 404);
        }

        $limit = (int) $request->input('limit', 15);
        $month = $request->input('month'); // 1-12
        $day = $request->input('tanggal'); // 1-31
        $year = (int) $request->input('year', (int) date('Y'));

        $query = Attendance::with(['user:id,name', 'employee:id,user_id,name', 'department:id,name'])
            ->where('employee_id', $employeeId)
            ->orderByDesc('tanggal')
            ->orderByDesc('created_at');

        // Date filters
        if ($month) {
            $query->whereYear('tanggal', $year)->whereMonth('tanggal', (int) $month);
        }
        if ($day) {
            $query->whereDay('tanggal', (int) $day);
        }

        $rows = $query->paginate($limit)->withQueryString();
        return AttendanceResource::collection($rows);
    }

    public function logAbsensi(Request $request)
    {
        $user = Auth::user();

        $employee = $user ? $user->employee : null;

        if (!$employee) {
            return response()->json(['message' => 'Employee not found'], 404);
        }

        $today = now()->format('Y-m-d');
        $todayAttendance = Attendance::where('employee_id', $employee->id)
            ->where('tanggal', $today)
            ->first();

        $todayAttendanceShiftWork = AttendanceShiftWork::where('employee_id', $employee->id)
            ->where('work_date', $today)
            ->first();

        // Get attendance history
        $query = Attendance::with(['employee.user', 'employee.department', 'shift'])
            ->where('employee_id', $employee->id)
            ->orderByDesc('tanggal')
            ->orderByDesc('created_at');

        if ($request->filled('date_from')) {
            $query->whereDate('tanggal', '>=', $request->input('date_from'));
        }
        if ($request->filled('date_to')) {
            $query->whereDate('tanggal', '<=', $request->input('date_to'));
        }

        $month = $request->input('month');
        $day = $request->input('tanggal');
        $year = (int) $request->input('year', (int) date('Y'));

        if ($month) {
            $query->whereYear('tanggal', $year)->whereMonth('tanggal', (int) $month);
        }
        if ($day) {
            $query->whereDay('tanggal', (int) $day);
        }

        $attendances = $query->get();

        // Transform attendance records into log entries
        $riwayatAbsen = [];
        foreach ($attendances as $attendance) {
            // Entry for check-in
            if ($attendance->jam_masuk) {
                $riwayatAbsen[] = [

                    'id' => $attendance->id,
                    'employee_id' => $attendance->employee_id,
                    'foto' => $attendance->foto_masuk ? asset('storage/' . $attendance->foto_masuk) : null,
                    'waktu' => $attendance->jam_masuk,
                    'keterangan' => 'masuk',
                    'tanggal' => $attendance->tanggal,
                ];
            }

            // Entry for check-out
            if ($attendance->jam_keluar) {
                $riwayatAbsen[] = [
                    'id' => $attendance->id,
                    'employee_id' => $attendance->employee_id,
                    'foto' => $attendance->foto_keluar ? asset('storage/' . $attendance->foto_keluar) : null,
                    'waktu' => $attendance->jam_keluar,
                    'keterangan' => 'keluar',
                    'tanggal' => $attendance->tanggal,
                ];
            }
        }

        return response()->json([
            'masuk_hari_ini' => $todayAttendance ? $todayAttendance->jam_masuk : null,
            'keluar_hari_ini' => $todayAttendance ? $todayAttendance->jam_keluar : null,
            'foto_masuk_hari_ini' => $todayAttendance ? $todayAttendance->foto_masuk : null,
            'foto_keluar_hari_ini' => $todayAttendance ? $todayAttendance->foto_keluar : null,
            'status_hari_ini' => $todayAttendance ? $todayAttendance->status : null,
            'shift' => $todayAttendanceShiftWork && $todayAttendanceShiftWork->shift ? $todayAttendanceShiftWork->shift->name : null,
            'nama' => $employee->name,
            'jabatan' => $employee->department ? $employee->department->name : null,
            'riwayat_absen' => $riwayatAbsen
        ]);
    }

    public function history(Request $request)
    {
        $user = Auth::user();
        $employee = $user ? $user->employee : null;
        $employeeId = $employee ? $employee->id : null;
        if (!$employeeId) {
            return response()->json(['message' => 'Employee not found'], 404);
        }

        $query = Attendance::with(['user:id,name', 'employee:id,user_id,name', 'department:id,name'])
            ->where('employee_id', $employeeId)
            ->orderByDesc('tanggal')
            ->orderByDesc('created_at');

        if ($request->filled('date_from')) {
            $query->whereDate('tanggal', '>=', $request->input('date_from'));
        }
        if ($request->filled('date_to')) {
            $query->whereDate('tanggal', '<=', $request->input('date_to'));
        }

        $month = $request->input('month'); // 1-12
        $day = $request->input('tanggal'); // 1-31
        $year = (int) $request->input('year', (int) date('Y'));

        if ($month) {
            $query->whereYear('tanggal', $year)->whereMonth('tanggal', (int) $month);
        }
        if ($day) {
            $query->whereDay('tanggal', (int) $day);
        }

        $rows = $query->get();

        return AttendanceResource::collection($rows);
    }

    public function show($id)
    {
        $attendance = Attendance::with(['user:id,name', 'employee:id,user_id,name', 'department:id,name'])
            ->findOrFail($id);

        return new AttendanceResource($attendance);
    }

    /**
     * POST /api/v1/attendances/hit
     * Create or update today's attendance with optional photo.
     * Accepts either multipart file 'photo' or base64 string 'photo_base64'.
     */
    public function hit(Request $request)
    {
        $user = Auth::user();
        $employee = $user ? $user->employee : null;
        if (!$employee) {
            return response()->json(['message' => 'Employee not found'], 404);
        }

        $request->validate([
            'latitude' => ['nullable', 'numeric'],
            'longitude' => ['nullable', 'numeric'],
            'distance' => ['nullable', 'numeric'],
            'photo' => ['nullable', 'file', 'max:10240'], // 10MB
            'photo_base64' => ['nullable', 'string'],
            'is_face_correct' => ['nullable', 'boolean'], // 0 = wajah benar, 1 = wajah salah (backend upgrade ke 2 jika salah di check-out)
        ]);

        // Timezone normalization
        $now = now('Asia/Makassar');
        $dateTime = $now->format('Y-m-d H:i:s');
        $currentTime = $now->format('H:i:s');

        // Prevent rapid duplicate attendance within 5 minutes
        $lastAttendance = Attendance::where('employee_id', $employee->id)
            ->whereDate('tanggal', $now->format('Y-m-d'))
            ->orderByDesc('updated_at')
            ->first();
        if ($lastAttendance && $lastAttendance->jam_masuk) {
            $lastAttendanceDateTime = \Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $now->format('Y-m-d') . ' ' . $lastAttendance->jam_masuk, 'Asia/Makassar');
            $timeDiff = $lastAttendanceDateTime->diffInMinutes($now);
            if ($timeDiff <= 5) {
                return response()->json([
                    'message' => 'Anda sudah absen beberapa menit lalu, silakan coba lagi nanti.',
                    'data' => ['type' => 'duplikat', 'last' => $lastAttendance->jam_masuk],
                ], 200);
            }
        }

        // Today record or create base row (ensures employee_id is always set)
        $attendance = Attendance::firstOrCreate(
            ['employee_id' => $employee->id, 'tanggal' => $now->format('Y-m-d')],
            ['department_id' => optional($employee->department)->id]
        );

        // Determine shift assignment for today
        $shiftWork = AttendanceShiftWork::where('employee_id', $employee->id)
            ->whereDate('work_date', $now->format('Y-m-d'))
            ->with('shift')
            ->first();
        $shift = $shiftWork ? $shiftWork->shift : null;

        // Decide status for check-in based on late tolerance
        $attendanceType = 1; // masuk
        $attendanceStatus = 'ON TIME';
        $message = 'Anda berhasil absen tepat waktu.';
        $keterangan = 'Absensi masuk tepat waktu';
        $lateMinutes = 0.0;

        if ($shift && $shift->late_tolerance) {
            $tz = 'Asia/Makassar';
            $ltRaw = trim((string) $shift->late_tolerance);
            $formats = ['Y-m-d H:i:s', 'Y-m-d H:i', 'H:i:s', 'H:i'];
            $tolDT = null;
            foreach ($formats as $fmt) {
                try {
                    $tolDT = \Carbon\Carbon::createFromFormat($fmt, $ltRaw, $tz);
                    break;
                } catch (\Throwable $e) {
                }
            }
            if ($tolDT) {
                $tolDT->setDate((int)$now->year, (int)$now->month, (int)$now->day);
                $currentDT = \Carbon\Carbon::createFromFormat('H:i:s', $currentTime, $tz)
                    ->setDate((int)$now->year, (int)$now->month, (int)$now->day);
                if ($currentDT->gt($tolDT)) {
                    $attendanceStatus = 'TERLAMBAT';
                    $attendanceType = 2;
                    $keterangan = 'Terlambat lebih dari toleransi';
                    $lateMinutes = round($tolDT->diffInSeconds($currentDT) / 60, 2);
                }
            }
        }

        // Save photo if any
        $photoPath = null;
        if ($request->hasFile('photo')) {
            $photoPath = $request->file('photo')->store('attendances', 'public');
        } elseif ($request->filled('photo_base64')) {
            $b64 = $request->input('photo_base64');
            if (preg_match('/^data:image\/(\w+);base64,/', $b64, $m)) {
                $ext = strtolower($m[1]);
                $data = base64_decode(substr($b64, strpos($b64, ',') + 1));
                $name = 'attendances/' . uniqid('att_') . '.' . $ext;
                Storage::disk('public')->put($name, $data);
                $photoPath = $name;
            }
        }

        // Check-in or check-out
        if (empty($attendance->jam_masuk)) {
            // Check-in
            $attendance->jam_masuk = $currentTime;
            $attendance->status = $attendanceStatus;
            $attendance->keterangan = $keterangan;
            if ($shift) $attendance->shift_id = $shift->id;
            if ($photoPath) $attendance->foto_masuk = $photoPath;
            if ($request->has('is_face_correct')) {
                // 0 = wajah benar, 1 = wajah salah saat masuk
                $attendance->is_face_correct = $request->boolean('is_face_correct') ? 1 : 0;
            }
            $attendance->late_duration_minutes = $lateMinutes;
            // $attendance->late_duration = (int) floor($lateMinutes);
            $type = 'masuk';

            // Send push notification to all staff
            try {
                $notificationService = app(\App\Services\NotificationService::class);
                $employeeName = $employee->name ?? 'Karyawan';
                $time = now('Asia/Makassar')->format('H:i');
                $notificationService->notifyAllStaffOnAttendance($employeeName, 'masuk', $time);
            } catch (\Throwable $e) {
                // Silent fail – notification should not block API
            }
        } else if (empty($attendance->jam_keluar)) {
            // Check-out; prevent too early checkout (min 15 minutes after check-in)
            if ($attendance->jam_masuk) {
                $jamMasuk = \Carbon\Carbon::createFromFormat('H:i:s', $attendance->jam_masuk, 'Asia/Makassar');
                $current = \Carbon\Carbon::createFromFormat('H:i:s', $currentTime, 'Asia/Makassar');
                if ($current->lt($jamMasuk->copy()->addMinutes(15))) {
                    return response()->json([
                        'message' => 'Anda belum dapat melakukan absensi pulang. Silakan coba lagi nanti.',
                    ], 200);
                }
            }
            $attendance->jam_keluar = $currentTime;
            if ($photoPath) $attendance->foto_keluar = $photoPath;
            if ($request->has('is_face_correct')) {
                $faceWrong = $request->boolean('is_face_correct');
                if ($faceWrong) {
                    // Jika check-in juga salah (is_face_correct = 1) → upgrade ke 2
                    // Jika check-in benar (is_face_correct = 0) → tetap 1
                    $attendance->is_face_correct = ($attendance->is_face_correct == 1) ? 2 : 1;
                } else {
                    // Wajah benar saat check-out, tidak ubah nilai check-in
                    // Biarkan nilai sebelumnya (0 atau 1) tetap
                }
            }
            // if check-out before shift end, mark as PULANG AWAL
            if ($shift && $currentTime < $shift->end_time) {
                $attendance->status = 'PULANG AWAL';
                $attendance->keterangan = 'Pulang lebih awal dari jadwal';
            } else {
                $attendance->status = 'PULANG';
                $attendance->keterangan = 'Absensi pulang tepat waktu';
            }
            $type = 'keluar';

            // Send push notification to all staff
            try {
                $notificationService = app(\App\Services\NotificationService::class);
                $employeeName = $employee->name ?? 'Karyawan';
                $time = now('Asia/Makassar')->format('H:i');
                $notificationService->notifyAllStaffOnAttendance($employeeName, 'keluar', $time);
            } catch (\Throwable $e) {
                // Silent fail – notification should not block API
            }
        } else {
            // Already complete today
            return response()->json([
                'message' => 'Anda telah menyelesaikan absensi hari ini.',
                'data' => ['type' => 'complete'],
            ], 200);
        }

        $attendance->save();

        return response()->json([
            'message' => 'Absensi berhasil',
            'data' => [
                'type' => $type,
                'employee' => [
                    'id' => $employee->id,
                    'name' => $employee->name,
                ],
                'attendance' => [
                    'tanggal' => $attendance->tanggal,
                    'jam_masuk' => $attendance->jam_masuk,
                    'jam_keluar' => $attendance->jam_keluar,
                    'status' => $attendance->status,
                    'keterangan' => $attendance->keterangan,
                    'foto_masuk' => $attendance->foto_masuk,
                    'foto_keluar' => $attendance->foto_keluar,
                    'late_duration_minutes' => $attendance->late_duration_minutes ?? null,
                ],
            ],
        ]);
    }
}
