<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\Department;
use App\Models\User;
use App\Models\Employee;
use App\Models\LeaveType;
use App\Models\Shift;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class AttendanceHistoryController extends Controller
{

    private const TOLERANCE_FORMATS = ['Y-m-d H:i:s', 'Y-m-d H:i', 'H:i:s', 'H:i'];
    private const TIMEZONE = 'Asia/Makassar';

    public function index(Request $request)
    {
        $q = $request->string('q')->toString();
        $branchId = $request->integer('branch_id') ?: null;
        $departmentId = $request->integer('department_id') ?: null;
        $shiftId = $request->integer('shift_id') ?: null;
        $status = $request->input('status'); // e.g. ON TIME, TERLAMBAT, ABSEN, dll
        // Default to today's date so we always show all employees with LEFT JOIN even if no attendance
        $date = $request->input('date') ?: now()->toDateString();
        $user = Auth::user();
        // Check if user is Superadmin
        $isSuperadmin = $user->hasRole('Superadmin');
        $userBranchId = $user->employee->branch_id ?? null;
        // If user status is "active" and no user_status filter from request, default to "active"
        $userStatus = $request->input('user_status') ?: ($user->status === 'active' ? 'active' : null);
        $dateFrom = $request->input('date_from');
        $dateTo = $request->input('date_to');
        $employeeId = $request->integer('employee_id') ?: null;
        $timeInFrom = $request->input('time_in_from');
        $timeInTo = $request->input('time_in_to');
        $timeOutFrom = $request->input('time_out_from');
        $timeOutTo = $request->input('time_out_to');


        $perPage = (int) $request->input('per_page', 10);

        // If a specific date is selected, show ALL employees with left-joined attendance of that date
        if (!empty($date)) {
            $empQuery = Employee::query()
                ->whereHas('attendanceShiftWorks', function ($query) use ($date) {
                    $query->where('work_date', '=', $date);
                })
                ->with([
                    'department:id,name',
                    'branch:id,name',
                    // Master shift bawaan employee (fallback jika tidak ada attendance_shift_works)
                    'shift:id,name,start_time,end_time',
                    // Jadwal harian dari attendance_shift_works untuk tanggal tersebut
                    'attendanceShiftWorks' => function ($query) use ($date) {
                        $query->where('work_date', '=', $date)
                            ->with(['shift:id,name,start_time,end_time']);
                    },
                    // Data jam masuk/keluar & status
                    'attendances' => function ($query) use ($date) {
                        $query->whereDate('tanggal', '=', $date)
                            ->orderByDesc('is_face_correct')
                            ->orderByDesc('id');
                    }
                ])
                ->select([
                    'employees.id',
                    'employees.name',
                    'employees.path',
                    'employees.gender',
                    'employees.department_id',
                    'employees.branch_id',
                    'employees.shift_id',
                    'employees.user_id',
                ]);

            if ($q) {
                // Map search keywords to status values
                $statusWord = strtolower(trim($q));
                $statusMap = [
                    'on time' => 'ON TIME',
                    'tepat waktu' => 'ON TIME',
                    'terlambat' => 'TERLAMBAT',
                    'pulang awal' => 'PULANG AWAL',
                    'pulang cepat' => 'PULANG AWAL',
                    'absen' => 'ABSEN',
                    'sedang berjalan' => 'SEDANG BERJALAN',
                    'lembur' => 'LEMBUR',
                    'cuti' => 'CUTI',
                    'izin' => 'IZIN',
                ];
                $mappedStatus = $statusMap[$statusWord] ?? null;

                $empQuery->where(function ($w) use ($q, $mappedStatus, $date) {
                    $w->where('employees.name', 'like', "%{$q}%")
                        ->orWhereHas('department', function ($d) use ($q) {
                            $d->where('name', 'like', "%{$q}%");
                        })
                        ->orWhereHas('branch', function ($b) use ($q) {
                            $b->where('name', 'like', "%{$q}%");
                        });
                    if ($mappedStatus) {
                        $w->orWhereHas('attendances', function ($a) use ($mappedStatus, $date) {
                            $a->where('status', $mappedStatus)
                                ->whereDate('tanggal', '=', $date);
                        });
                    }
                });
            }
            if ($branchId) {
                $empQuery->where('employees.branch_id', $branchId);
            }
            if ($departmentId) {
                $empQuery->where('employees.department_id', $departmentId);
            }
            if ($shiftId) {
                // Filter berdasarkan shift harian (attendance_shift_works.shift_id)
                $empQuery->whereHas('attendanceShiftWorks', function ($q) use ($shiftId, $date) {
                    $q->where('shift_id', $shiftId)
                        ->where('work_date', '=', $date);
                });
            }
            if ($employeeId) {
                $empQuery->where('employees.id', $employeeId);
            }
            if (!empty($status) && $status != "ABSEN") {
                $empQuery->whereHas('attendances', function ($query) use ($status, $date) {
                    $query->where('status', $status)
                        ->whereDate('tanggal', '=', $date);
                });
            }
            if (!empty($dateFrom)) {
                $empQuery->whereHas('attendances', function ($query) use ($dateFrom) {
                    $query->where('tanggal', '>=', $dateFrom);
                });
            }
            if (!empty($dateTo)) {
                $empQuery->whereHas('attendances', function ($query) use ($dateTo) {
                    $query->where('tanggal', '<=', $dateTo);
                });
            }
            if (!empty($status) && $status == "ABSEN") {
                $empQuery->whereDoesntHave('attendances', function ($query) use ($date) {
                    $query->whereDate('tanggal', '=', $date);
                });
            }
            if (!empty($timeInFrom)) {
                $empQuery->whereHas('attendances', function ($query) use ($timeInFrom, $date) {
                    $query->where('jam_masuk', '>=', $timeInFrom)
                        ->whereDate('tanggal', '=', $date);
                });
            }
            if (!empty($timeInTo)) {
                $empQuery->whereHas('attendances', function ($query) use ($timeInTo, $date) {
                    $query->where('jam_masuk', '<=', $timeInTo)
                        ->whereDate('tanggal', '=', $date);
                });
            }
            if (!empty($timeOutFrom)) {
                $empQuery->whereHas('attendances', function ($query) use ($timeOutFrom, $date) {
                    $query->where('jam_keluar', '>=', $timeOutFrom)
                        ->whereDate('tanggal', '=', $date);
                });
            }
            if (!empty($timeOutTo)) {
                $empQuery->whereHas('attendances', function ($query) use ($timeOutTo, $date) {
                    $query->where('jam_keluar', '<=', $timeOutTo)
                        ->whereDate('tanggal', '=', $date);
                });
            }

            // Filter by branch_id if user is not Superadmin and not branch_id 2
            if (!$isSuperadmin && $userBranchId && $userBranchId != 2) {
                $empQuery->where('employees.branch_id', $userBranchId);
            }

            // Filter by user status (Active, Inactive, Pending)
            if ($userStatus) {
                $empQuery->whereHas('user', function ($query) use ($userStatus) {
                    $query->where('status', $userStatus);
                });
            }

            // Calculate summary from all data (before pagination)
            $allRows = (clone $empQuery)->orderBy('employees.name')->get();
            $summary = [
                'running' => 0,
                'complete' => 0,
                'sakit' => 0,
                'cuti' => 0,
                'izin' => 0,
                'absen' => 0,
            ];

            foreach ($allRows as $employee) {
                // Ambil attendance dari relasi attendances yg sudah difilter per tanggal
                $attendance = $employee->attendances->first();

                // Fallback: jika belum ada (harusnya jarang terjadi), cari langsung di tabel attendances
                if (!$attendance) {
                    $attendance = Attendance::where('employee_id', $employee->id)
                        ->whereDate('tanggal', '=', $date)
                        ->orderByDesc('is_face_correct')
                        ->orderByDesc('id')
                        ->first();
                }

                $jamMasuk = $attendance ? $attendance->jam_masuk : null;
                $jamKeluar = $attendance ? $attendance->jam_keluar : null;
                $status = $attendance ? $attendance->status : null;

                $running = !empty($jamMasuk) && empty($jamKeluar);

                // Match frontend logic: RUNNING takes priority
                if ($running) {
                    $summary['running']++;
                } elseif ($status === 'COMPLETE') {
                    $summary['complete']++;
                } elseif ($status === 'SAKIT') {
                    $summary['sakit']++;
                } elseif ($status === 'CUTI') {
                    $summary['cuti']++;
                } elseif ($status === 'IZIN') {
                    $summary['izin']++;
                } elseif ($status === 'ABSEN' || (empty($jamMasuk) && empty($jamKeluar) && empty($status))) {
                    $summary['absen']++;
                }
            }

            $faceMismatchQuery = Attendance::query()
                ->with([
                    'employee:id,name,branch_id',
                    'employee.branch:id,name',
                ])
                ->where('is_face_correct', '>=', 1);

            if (!$isSuperadmin && $userBranchId && $userBranchId != 2) {
                $faceMismatchQuery->whereHas('employee', function ($query) use ($userBranchId) {
                    $query->where('branch_id', $userBranchId);
                });
            }

            $faceMismatchCount = (clone $faceMismatchQuery)->count();
            $faceMismatchItems = (clone $faceMismatchQuery)
                ->orderByDesc('tanggal')
                ->orderByDesc('id')
                ->limit(100)
                ->get()
                ->map(function ($attendance) {
                    return [
                        'id' => $attendance->id,
                        'date' => $attendance->tanggal,
                        'employee' => [
                            'id' => optional($attendance->employee)->id,
                            'name' => optional($attendance->employee)->name,
                        ],
                        'branch' => optional(optional($attendance->employee)->branch)->name,
                        'jam_masuk' => $attendance->jam_masuk,
                        'jam_keluar' => $attendance->jam_keluar,
                        'status' => $attendance->status,
                        'is_face_correct' => $attendance->is_face_correct,
                    ];
                })
                ->values();

            $attendances = $empQuery->orderBy('employees.name')->paginate($perPage)->withQueryString();


            // dd($attendances->toArray());
            return Inertia::render('Admin/Attendance/Index', [
                'attendances' => $attendances->through(function ($employee) use ($date) {
                    // Data kehadiran (jam masuk/keluar & status)
                    $attendance = $employee->attendances->first();

                    // Fallback: jika tidak ada attendance yang terhubung ke shift_work,
                    // ambil langsung dari tabel attendances berdasarkan employee_id dan tanggal
                    if (!$attendance) {
                        $attendance = Attendance::where('employee_id', $employee->id)
                            ->whereDate('tanggal', '=', $date)
                            ->orderByDesc('is_face_correct')
                            ->orderByDesc('id')
                            ->first();
                    }

                    // compute duration if both times present
                    $duration = null;
                    $jamMasuk = $attendance ? $attendance->jam_masuk : null;
                    $jamKeluar = $attendance ? $attendance->jam_keluar : null;

                    if (!empty($jamMasuk) && !empty($jamKeluar)) {
                        try {
                            $start = \Carbon\Carbon::createFromFormat('H:i:s', $jamMasuk);
                            $end = \Carbon\Carbon::createFromFormat('H:i:s', $jamKeluar);
                            if ($end->lt($start)) {
                                $end->addDay();
                            }
                            $minutes = $start->diffInMinutes($end);
                            $h = floor($minutes / 60);
                            $m = $minutes % 60;
                            $duration = $h . ' Jam ' . str_pad($m, 2, '0', STR_PAD_LEFT) . ' Mnt';
                        } catch (\Throwable $e) {
                            $duration = null;
                        }
                    }

                    // Jadwal harian diambil dari attendance_shift_works jika ada,
                    // fallback ke master shift di tabel employees jika tidak ada.
                    $shiftWork = $employee->attendanceShiftWorks->first();
                    $shiftFromWork = $shiftWork && $shiftWork->shift ? $shiftWork->shift : null;

                    return [
                        'id' => $attendance ? $attendance->id : null,
                        'date' => $attendance ? $attendance->tanggal : $date,
                        'employee' => [
                            'id' => $employee->id,
                            'name' => $employee->name,
                            'photo_url' => $employee->path,
                            'gender' => $employee->gender,
                        ],
                        'branch' => $employee->branch ? $employee->branch->name : null,
                        'department' => $employee->department ? $employee->department->name : null,
                        'shift' => [
                            'name' => $shiftFromWork ? $shiftFromWork->name : ($employee->shift ? $employee->shift->name : null),
                            'start_time' => $shiftFromWork ? $shiftFromWork->start_time : ($employee->shift ? $employee->shift->start_time : null),
                            'end_time' => $shiftFromWork ? $shiftFromWork->end_time : ($employee->shift ? $employee->shift->end_time : null),
                        ],
                        'jam_masuk' => $jamMasuk,
                        'jam_keluar' => $jamKeluar,
                        'durasi' => $duration,
                        'status' => $attendance ? $attendance->status : null,
                        'keterangan' => $attendance ? $attendance->keterangan : null,
                        'foto_masuk' => $attendance && $attendance->foto_masuk ? Storage::url($attendance->foto_masuk) : null,
                        'foto_keluar' => $attendance && $attendance->foto_keluar ? Storage::url($attendance->foto_keluar) : null,
                        'is_face_correct' => $attendance ? $attendance->is_face_correct : null,
                    ];
                }),
                'filters' => [
                    'q' => $q,
                    'branch_id' => $branchId,
                    'department_id' => $departmentId,
                    'shift_id' => $shiftId,
                    'status' => $status,
                    'date' => $date,
                    'employee_id' => $employeeId,
                    'time_in_from' => $timeInFrom,
                    'time_in_to' => $timeInTo,
                    'time_out_from' => $timeOutFrom,
                    'time_out_to' => $timeOutTo,
                    'user_status' => $userStatus,
                ],
                'branches' => $this->getFilteredBranches($isSuperadmin, $userBranchId),
                'departments' => $this->getFilteredDepartments($isSuperadmin, $userBranchId),
                'employees' => $this->getFilteredEmployees($isSuperadmin, $userBranchId),
                'shifts' => Shift::select('id', 'name', 'start_time', 'end_time')->orderBy('name')->get(),
                'specialLeaveTypes' => LeaveType::query()
                    ->where('category', 'special_leave')
                    ->select('id', 'name')
                    ->orderBy('name')
                    ->get(),
                // Updated status options
                'status_options' => ['RUNNING', 'COMPLETE', 'SAKIT', 'CUTI', 'ABSEN','IZIN'],
                'summary' => $summary,
                'face_mismatch_count' => $faceMismatchCount,
                'face_mismatch_items' => $faceMismatchItems,
            ]);
        }
    }


    public function show($id)
    {
        $attendance = Attendance::with([
            'employee:id,name,path,file_name,shift_id,branch_id',
            'department:id,name',
            'employee.shift:id,name',
            'employee.branch:id,name',
        ])->findOrFail($id);

        $shift = optional($attendance->employee)->shift;
        $branch = optional($attendance->employee)->branch;

        $duration = null;
        if (!empty($attendance->jam_masuk) && !empty($attendance->jam_keluar)) {
            try {
                $start = \Carbon\Carbon::createFromFormat('H:i:s', $attendance->jam_masuk);
                $end = \Carbon\Carbon::createFromFormat('H:i:s', $attendance->jam_keluar);
                if ($end->lessThan($start)) {
                    $end->addDay();
                }
                $minutes = $end->diffInMinutes($start, false) * -1;
                $h = floor($minutes / 60);
                $m = $minutes % 60;
                $duration = $h . ' Jam ' . str_pad($m, 2, '0', STR_PAD_LEFT) . ' Mnt';
            } catch (\Throwable $e) {
                $duration = null;
            }
        }

        return Inertia::render('Admin/Attendance/Index', [
            'attendance' => [
                'id' => $attendance->id,
                'date' => $attendance->tanggal,
                'employee' => [
                    'id' => optional($attendance->employee)->id,
                    'name' => optional($attendance->employee)->name,
                    'photo_url' => optional($attendance->employee)->photo_url,
                ],
                'shift' => [
                    'id' => optional($shift)->id,
                    'name' => optional($shift)->name,
                ],
                'branch' => [
                    'id' => optional($branch)->id,
                    'name' => optional($branch)->name
                ],
                'foto_masuk' => Storage::url($attendance->foto_masuk),
                'foto_keluar' => Storage::url($attendance->foto_keluar),
                'department' => optional($attendance->department)->name,
                'jam_masuk' => $attendance->jam_masuk,
                'jam_keluar' => $attendance->jam_keluar,
                'durasi' => $duration,
                'status' => $attendance->status,
                'keterangan' => $attendance->keterangan,
            ],
            'openShowModal' => true,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'employee_id' => ['required', 'exists:employees,id'],
            'tanggal' => ['required', 'date'],
            'jam_masuk' => ['nullable', 'date_format:H:i:s'],
            'jam_keluar' => ['nullable', 'date_format:H:i:s'],
            'status' => ['nullable', 'string'],
            'keterangan' => ['nullable', 'string'],
        ]);

        $employee = Employee::findOrFail($request->input('employee_id'));

        // Check if attendance already exists for this employee and date
        $attendance = Attendance::where('employee_id', $employee->id)
            ->whereDate('tanggal', $request->input('tanggal'))
            ->first();

        if ($attendance) {
            // If exists, update instead
            return $this->update($request, $attendance->id);
        }

        // Get shift work from employee or from shift work for the date
        $shiftWork = \App\Models\AttendanceShiftWork::where('employee_id', $employee->id)
            ->whereDate('work_date', $request->input('tanggal'))
            ->with('shift')
            ->first();

        // Create new attendance
        $attendanceData = [
            'employee_id' => $employee->id,
            'department_id' => $employee->department_id,
            'tanggal' => $request->input('tanggal'),
            'jam_masuk' => $request->input('jam_masuk'),
            'jam_keluar' => $request->input('jam_keluar'),
            'status' => $request->input('status'),
            'keterangan' => $request->input('keterangan'),
            'shift_work_id' => $shiftWork->id,
        ];

        if ($shiftWork && $shiftWork->shift) {
            $attendanceData['shift_id'] = $shiftWork->shift->id;

            // Late duration calculation
            if ($request->filled('jam_masuk') && $shiftWork->shift->late_tolerance) {
                $attendanceDate = Carbon::parse($request->input('tanggal'), self::TIMEZONE);
                $jamMasuk = $request->input('jam_masuk');

                $toleranceDateTime = $this->parseToleranceTime($shiftWork->shift->late_tolerance, $attendanceDate);

                if ($toleranceDateTime) {
                    $jamMasukDateTime = $this->createCurrentDateTime($jamMasuk, $attendanceDate);

                    if ($jamMasukDateTime->gt($toleranceDateTime)) {
                        $lateMinutes = round($toleranceDateTime->diffInSeconds($jamMasukDateTime) / 60, 2);
                        $attendanceData['late_duration_minutes'] = (int) floor($lateMinutes);
                    } else {
                        $attendanceData['late_duration_minutes'] = 0;
                    }
                }
            }
        }

        $attendance = Attendance::create($attendanceData);

        return redirect()->route('presences.index')->with('success', 'Absensi berhasil dibuat');
    }

    public function update(Request $request, $id)
    {
        $attendance = Attendance::with('shift')->findOrFail($id);

        $request->validate([
            'jam_masuk' => ['nullable', 'date_format:H:i:s'],
            'jam_keluar' => ['nullable', 'date_format:H:i:s'],
            'status' => ['nullable', 'string'],
            'keterangan' => ['nullable', 'string'],
        ]);

        $updateData = [
            'jam_masuk' => $request->input('jam_masuk'),
            'jam_keluar' => $request->input('jam_keluar'),
            'status' => $request->input('status'),
            'keterangan' => $request->input('keterangan'),
        ];

        // Late duration calculation (same logic as AbsensiChekIn.php)
        if ($request->filled('jam_masuk') && $attendance->shift && $attendance->shift->late_tolerance) {
            // Use attendance date instead of current date
            $attendanceDate = Carbon::parse($attendance->tanggal, self::TIMEZONE);
            $jamMasuk = $request->input('jam_masuk');

            $toleranceDateTime = $this->parseToleranceTime($attendance->shift->late_tolerance, $attendanceDate);

            if ($toleranceDateTime) {
                $jamMasukDateTime = $this->createCurrentDateTime($jamMasuk, $attendanceDate);

                if ($jamMasukDateTime->gt($toleranceDateTime)) {
                    // Calculate late minutes: difference in seconds divided by 60, rounded to 2 decimals
                    $lateMinutes = round($toleranceDateTime->diffInSeconds($jamMasukDateTime) / 60, 2);
                    // Store as integer floor (same as AbsensiChekIn.php)
                    $updateData['late_duration_minutes'] = (int) floor($lateMinutes);
                } else {
                    // If on time or within tolerance, set to 0
                    $updateData['late_duration_minutes'] = 0;
                }
            }
        }

        $attendance->update($updateData);

        return redirect()->route('presences.index')->with('success', 'Absensi berhasil diupdate');
    }

    /**
     * Correct the employee_id on an attendance record (when face recognition was wrong).
     * Moves the attendance to the correct employee, updating department and shift_work as well.
     */
    public function correctEmployee(Request $request, $id)
    {
        $request->validate([
            'employee_id' => ['required', 'exists:employees,id'],
        ]);

        $attendance = Attendance::findOrFail($id);
        $newEmployee = Employee::findOrFail($request->input('employee_id'));

        // Prevent duplicate: check if new employee already has attendance on that date
        $existing = Attendance::where('employee_id', $newEmployee->id)
            ->whereDate('tanggal', $attendance->tanggal)
            ->where('id', '!=', $attendance->id)
            ->first();

        if ($existing) {
            return back()->withErrors(['employee_id' => 'Karyawan ini sudah memiliki data absensi pada tanggal tersebut.']);
        }

        // Find the correct shift_work_id for the new employee on that date
        $shiftWork = \App\Models\AttendanceShiftWork::where('employee_id', $newEmployee->id)
            ->whereDate('work_date', $attendance->tanggal)
            ->first();

        $attendance->employee_id = $newEmployee->id;
        $attendance->department_id = $newEmployee->department_id;
        $attendance->is_face_correct = 0; // Corrected by admin = now verified correct (no longer flagged)

        if ($shiftWork) {
            $attendance->shift_work_id = $shiftWork->id;
            $attendance->shift_id = $shiftWork->shift_id;
        }

        $attendance->save();

        return redirect()->route('presences.index', ['date' => $attendance->tanggal])
            ->with('success', "Absensi berhasil dikoreksi ke karyawan {$newEmployee->name}.");
    }

    /**
     * Parse tolerance time from various formats (same logic as AbsensiChekIn.php)
     */
    private function parseToleranceTime($toleranceRaw, Carbon $now): ?Carbon
    {
        $toleranceString = trim((string) $toleranceRaw);

        foreach (self::TOLERANCE_FORMATS as $format) {
            try {
                $toleranceDateTime = Carbon::createFromFormat($format, $toleranceString, self::TIMEZONE);
                // Normalize to today's date for comparison
                $toleranceDateTime->setDate($now->year, $now->month, $now->day);
                return $toleranceDateTime;
            } catch (\Throwable $e) {
                continue;
            }
        }

        return null;
    }

    /**
     * Create current datetime for comparison (same logic as AbsensiChekIn.php)
     */
    private function createCurrentDateTime(string $currentTime, Carbon $now): Carbon
    {
        return Carbon::createFromFormat('H:i:s', $currentTime, self::TIMEZONE)
            ->setDate($now->year, $now->month, $now->day);
    }

    /**
     * Get filtered departments based on user role
     */
    private function getFilteredDepartments(bool $isSuperadmin, ?int $branchId)
    {
        $query = Department::with('branch:id,name')->select('id', 'name', 'branch_id')->orderBy('name');

        if (!$isSuperadmin && $branchId && $branchId != 2) {
            $query->where('branch_id', $branchId);
        }

        return $query->get();
    }

    /**
     * Get filtered branches based on user role
     */
    private function getFilteredBranches(bool $isSuperadmin, ?int $branchId)
    {
        $query = \App\Models\Branch::select('id', 'name')->orderBy('name');

        if (!$isSuperadmin && $branchId && $branchId != 2) {
            $query->where('id', $branchId);
        }

        return $query->get();
    }

    /**
     * Get filtered employees based on user role
     */
    private function getFilteredEmployees(bool $isSuperadmin, ?int $branchId)
    {
        $query = Employee::select('id', 'name')->orderBy('name')->limit(500);

        if (!$isSuperadmin && $branchId && $branchId != 2) {
            $query->where('branch_id', $branchId);
        }

        return $query->get();
    }
}
