<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\AttendanceShiftWork;
use App\Models\Branch;
use App\Models\Department;
use App\Models\Employee;
use App\Models\Shift;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class AttendanceShiftWorkController extends Controller
{
    public function index(Request $request)
    {
        // Hak akses: lihat daftar shift kerja
        abort_unless(Gate::allows('work_shifts.view'), 403, 'Anda tidak memiliki akses untuk melihat data shift kerja');

        $isSuperadmin = Auth::user()->hasRole("Superadmin");
        $search = $request->string('q')->toString();
        $employeeId = $request->integer('employee_id') ?: null;
        $shiftId = $request->integer('shift_id') ?: null;
        $dateFrom = $request->input('date_from');
        $dateTo = $request->input('date_to');
        $monthParam = $request->input('month'); // expected format YYYY-MM
        $yearParam = $request->input('year');
        $groupBy = $request->input('groupBy'); // department, shift, work_date, null

        $perPage = max(1, (int) $request->input('per_page', 14));

        $query = AttendanceShiftWork::query()
            // Join attendance to get daily status for the same employee on the same work_date
            ->leftJoin('attendances', function ($join) {
                $join->on('attendances.employee_id', '=', 'attendance_shift_works.employee_id')
                    ->on('attendances.tanggal', '=', 'attendance_shift_works.work_date');
            })
            ->with([
                'employee:id,name,user_id,gender,path',
                'employee.user.roles:id,name',
                'department:id,name',
                'shift:id,code,name,start_time,late_tolerance,end_time',
            ])
            ->select([
                'attendance_shift_works.*',
                'attendances.status as att_status',
                'attendances.jam_masuk as att_jam_masuk',
                'attendances.jam_keluar as att_jam_keluar',
            ])
            ->when(!$isSuperadmin && Auth::user()->employee->branch_id != 2, function ($query) {
                $query->whereHas('employee', function ($q) {
                    $q->where('branch_id', Auth::user()->employee->branch_id);
                });
            });

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->whereHas('employee', function ($sub) use ($search) {
                    $sub->where('name', 'like', "%{$search}%");
                })
                    ->orWhereHas('department', function ($sub) use ($search) {
                        $sub->where('name', 'like', "%{$search}%");
                    })
                    ->orWhereHas('shift', function ($sub) use ($search) {
                        $sub->where('name', 'like', "%{$search}%");
                    });
            });
        }

        if ($employeeId) {
            $query->where('attendance_shift_works.employee_id', $employeeId);
        }
        if ($shiftId) {
            $query->where('attendance_shift_works.shift_id', $shiftId);
        }
        // Month filter has priority over date range
        if (!empty($monthParam)) {
            try {
                $dt = \Carbon\Carbon::createFromFormat('Y-m', $monthParam)->startOfMonth();
                $end = (clone $dt)->endOfMonth();
                $query->whereBetween('work_date', [$dt->toDateString(), $end->toDateString()]);
            } catch (\Throwable $e) {
                // If month format is invalid, fall through to date range filter
            }
        }

        // Apply date range filter if month filter is not used
        if (empty($monthParam)) {
            // Apply year filter if provided
            if (!empty($yearParam) && is_numeric($yearParam)) {
                $query->whereYear('work_date', (int) $yearParam);
            }

            // Apply date range filter (date_from and/or date_to)
            if (!empty($dateFrom)) {
                $query->whereDate('work_date', '>=', $dateFrom);
            }
            if (!empty($dateTo)) {
                $query->whereDate('work_date', '<=', $dateTo);
            }
        }

        $assignments = $query->orderByDesc('attendance_shift_works.id')->paginate($perPage)->withQueryString();

        return Inertia::render('Admin/ShiftWorks/Index', [
            'assignments' => $assignments->through(function ($row) {
                return [
                    'id' => $row->id,
                    'employee' => [
                        'id' => optional($row->employee)->id,
                        'name' => optional($row->employee)->name,
                        'photo_url' => optional($row->employee)->path,
                        'role' => $row->employee?->user?->roles?->pluck('name')->first(),
                        'gender' => optional($row->employee)->gender,
                    ],
                    'department' => optional($row->department)->name,
                    'shift' => [
                        'id' => optional($row->shift)->id,
                        'code' => optional($row->shift)->code,
                        'name' => optional($row->shift)->name,
                        'start_time' => optional($row->shift)->start_time ? \Carbon\Carbon::parse($row->shift->start_time)->format('H:i') : null,
                        'late_tolerance' => optional($row->shift)->late_tolerance,
                        'end_time' => optional($row->shift)->end_time ? \Carbon\Carbon::parse($row->shift->end_time)->format('H:i') : null,
                    ],
                    'work_date' => $row->work_date ? \Carbon\Carbon::parse($row->work_date)->format('Y-m-d') : null,
                    'created_at' => optional($row->created_at) ? $row->created_at->format('Y-m-d H:i:s') : null,
                    // Attendance status of that day (if exists)
                    'status' => $row->att_status,
                    'jam_masuk' => $row->att_jam_masuk,
                    'jam_keluar' => $row->att_jam_keluar,
                ];
            }),
            'q' => $search,
            // datasets for bulk modal
            'employees' => Employee::select('id', 'name', 'user_id', 'branch_id', 'department_id')->when(!$isSuperadmin && Auth::user()->employee->branch_id != 2, function ($query) {
                $query->where('branch_id', Auth::user()->employee->branch_id);
            })->orderBy('name')->limit(500)->get(),
            'shifts' => Shift::select('id', 'name', 'start_time', 'end_time', 'late_tolerance')->orderBy('name')->get(),
            'branches' => Branch::select('id', 'name')->when(!$isSuperadmin && Auth::user()->employee->branch_id != 2, function ($query) {
                $query->where('id', Auth::user()->employee->branch_id);
            })->orderBy('name')->get(),
            'departments' => Department::select('id', 'name', 'branch_id')->when(!$isSuperadmin && Auth::user()->employee->branch_id != 2, function ($query) {
                $query->where('branch_id', Auth::user()->employee->branch_id);
            })->orderBy('name')->get(),
            'filters' => [
                'employee_id' => $employeeId,
                'shift_id' => $shiftId,
                'date_from' => $dateFrom,
                'date_to' => $dateTo,
                'month' => $monthParam,
                'year' => $yearParam,
                'per_page' => $perPage,
            ],
            'groupBy' => in_array($groupBy, ['department', 'shift', 'work_date'], true) ? $groupBy : null,
        ]);
    }

    public function create()
    {
        // Hak akses: buka form tambah
        abort_unless(Gate::allows('work_shifts.create'), 403, 'Anda tidak memiliki akses untuk menambah shift kerja');

        return Inertia::render('Admin/ShiftWorks/Create', [
            'employees' => User::select('id', 'name')->orderBy('name')->limit(500)->get(),
            'shifts' => Shift::select('id', 'name', 'start_time', 'end_time', 'late_tolerance')->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request)
    {
        abort_unless(Gate::allows('work_shifts.create'), 403, 'Anda tidak memiliki akses untuk menambah shift kerja');

        $validated = $request->validate([
            'employee_id' => 'nullable|integer|exists:employees,id',
            'user_id' => 'nullable|integer',
            'user_ids' => 'nullable|array',
            'user_ids.*' => 'integer',
            'shift_id' => 'required|exists:shifts,id',
            'work_date' => 'nullable|date',
            'date_from' => 'nullable|date',
            'date_to' => 'nullable|date',
            'all_employees' => 'sometimes|boolean',
            'branch_id' => 'nullable|integer|exists:branches,id',
            'department_id' => 'nullable|integer|exists:departments,id',
        ]);

        $employeeId = $validated['employee_id'] ?? $validated['user_id'] ?? null;
        $userIds = $validated['user_ids'] ?? [];
        $shiftId = (int) $validated['shift_id'];
        $allEmployees = (bool) ($validated['all_employees'] ?? false);

        $from = $validated['date_from'] ?? $validated['work_date'] ?? null;
        $to = $validated['date_to'] ?? $validated['work_date'] ?? null;

        if (!$from) {
            return back()->withErrors(['date_from' => 'Tanggal mulai wajib diisi.']);
        }
        if (!$to)
            $to = $from;

        $start = \Carbon\Carbon::parse($from)->startOfDay();
        $end = \Carbon\Carbon::parse($to)->startOfDay();
        if ($end->lessThan($start))
            [$start, $end] = [$end, $start];

        $employeeQuery = Employee::query();
        if (!empty($validated['branch_id']))
            $employeeQuery->where('branch_id', $validated['branch_id']);
        if (!empty($validated['department_id']))
            $employeeQuery->where('department_id', $validated['department_id']);

        if ($allEmployees) {
            $employeeIds = $employeeQuery->pluck('id')->all();
        } elseif (!empty($userIds)) {
            $employeeIds = $employeeQuery->whereIn('user_id', $userIds)->pluck('id')->all();
            if (empty($employeeIds)) {
                return back()->withErrors(['user_ids' => 'Tidak ada karyawan yang valid ditemukan.']);
            }
        } else {
            if (!$employeeId) {
                return back()->withErrors(['employee_id' => 'Karyawan wajib dipilih atau checklist Semua karyawan.']);
            }
            $employeeIds = [(int) $employeeId];
        }

        // Grab shift weekly pattern if exists
        $pattern = [];
        $shiftModel = \App\Models\Shift::find($shiftId);
        if ($shiftModel && $shiftModel->weekly_pattern) {
            $pattern = (array) $shiftModel->weekly_pattern;
        }
        $dayNames = ['sun', 'mon', 'tue', 'wed', 'thu', 'fri', 'sat'];

        $rows = [];
        $now = now();
        $cursor = $start->copy();
        while ($cursor->lte($end)) {
            $dateString = $cursor->toDateString();
            $dow = $dayNames[$cursor->dayOfWeek];
            $applyShift = empty($pattern) || !empty($pattern[$dow]);
            if ($applyShift) {
                foreach ($employeeIds as $eid) {
                    $employee = Employee::find($eid);
                    $rows[] = [
                        'employee_id' => $eid,
                        'department_id' => $employee->department_id,
                        'shift_id' => $shiftId,
                        'work_date' => $dateString,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ];
                }
            }
            $cursor->addDay();
        }

        if (empty($rows)) {
            return back()->with('error', 'Tidak ada data untuk disimpan.');
        }

        // Filter duplicate: Only save new shift assignments
        $rowKeys = collect($rows)->map(fn($row) => $row['employee_id'] . '|' . $row['work_date']);
        $empIds = collect($rows)->pluck('employee_id')->unique()->values();
        $dates = collect($rows)->pluck('work_date')->unique()->values();

        $existing = AttendanceShiftWork::whereIn('employee_id', $empIds)
            ->whereIn('work_date', $dates)
            ->with('employee:id,name')
            ->get(['employee_id', 'work_date']);

        $existingKeys = $existing->map(fn($ex) => $ex->employee_id . '|' . $ex->work_date)->toArray();

        $filteredRows = array_filter($rows, function ($row) use ($existingKeys) {
            return !in_array($row['employee_id'] . '|' . $row['work_date'], $existingKeys);
        });

        if (empty($filteredRows)) {
            // Group duplicates by employee
            $duplicatesByEmployee = $existing->groupBy('employee_id')->map(function ($items, $empId) {
                $employee = $items->first()->employee;
                $dates = $items->pluck('work_date')->map(function ($date) {
                    return \Carbon\Carbon::parse($date)->format('d-m-Y');
                })->sort()->values()->all();
                return [
                    'employee_id' => $empId,
                    'employee_name' => $employee ? $employee->name : 'Unknown',
                    'dates' => $dates,
                ];
            })->values()->all();

            $errorMessage = 'Data shift kerja yang sama sudah pernah ada. ';
            $errorMessage .= 'Karyawan yang sudah memiliki shift: ';

            $employeeDetails = [];
            foreach ($duplicatesByEmployee as $dup) {
                $datesStr = implode(', ', $dup['dates']);
                $employeeDetails[] = "{$dup['employee_name']} (tanggal: {$datesStr})";
            }

            $errorMessage .= implode('; ', $employeeDetails) . '.';

            return back()->with('error', $errorMessage)->with('duplicates', $duplicatesByEmployee);
        }

        DB::transaction(function () use ($filteredRows) {
            try {
                AttendanceShiftWork::upsert($filteredRows, ['employee_id', 'work_date'], ['shift_id', 'updated_at']);
            } catch (\Throwable $e) {
                $grouped = collect($filteredRows)->groupBy('employee_id');
                foreach ($grouped as $eid => $items) {
                    $dates = collect($items)->pluck('work_date')->unique()->values();
                    AttendanceShiftWork::where('employee_id', $eid)->whereIn('work_date', $dates)->delete();
                }
                foreach (array_chunk($filteredRows, 1000) as $chunk) {
                    AttendanceShiftWork::insert($chunk);
                }
            }
        });

        return redirect()->route('shift-works.index')
            ->with('success', 'Shift berhasil ditambahkan.');
    }

    public function edit(AttendanceShiftWork $attendanceShiftWork)
    {
        // Hak akses: (legacy) edit – saat ini tidak dipakai oleh Inertia
        abort_unless(Gate::allows('work_shifts.edit'), 403, 'Anda tidak memiliki akses untuk mengubah shift kerja');

        return view('attendance_shift_works.edit', compact('attendanceShiftWork'));
    }

    public function bulkStore(Request $request)
    {
        // Hak akses: tambah banyak
        abort_unless(Gate::allows('work_shifts.create'), 403, 'Anda tidak memiliki akses untuk menambah shift kerja');

        $data = $request->validate([
            'month' => ['required', 'date_format:Y-m'],
            'employee_ids' => ['required', 'array', 'min:1'],
            'employee_ids.*' => ['integer', 'exists:users,id'],
            'shift_id' => ['required', 'exists:shifts,id'],
            'pattern.type' => ['required', 'in:every_day,weekdays,custom_days'],
            'pattern.days' => ['nullable', 'array'],
            'pattern.days.*' => ['integer', 'min:1', 'max:31'],
            'overwrite' => ['sometimes', 'boolean'],
        ]);

        $month = $data['month'];
        $dt = \Carbon\Carbon::createFromFormat('Y-m', $month)->startOfMonth();
        $lastDay = (int) $dt->copy()->endOfMonth()->format('d');

        // Build list of days
        $days = [];
        $type = $data['pattern']['type'];
        if ($type === 'every_day') {
            $days = range(1, $lastDay);
        } elseif ($type === 'weekdays') {
            for ($d = 1; $d <= $lastDay; $d++) {
                $day = $dt->copy()->day($d);
                if (!$day->isWeekend())
                    $days[] = $d;
            }
        } else { // custom_days
            $days = array_values(array_filter($data['pattern']['days'] ?? [], function ($n) use ($lastDay) {
                return $n >= 1 && $n <= $lastDay;
            }));
        }

        if (empty($days)) {
            return back()->with('error', 'Tidak ada hari yang dipilih.');
        }

        $now = now();
        $rows = [];
        foreach ($data['employee_ids'] as $uid) {
            foreach ($days as $d) {
                $workDate = $dt->copy()->day($d)->toDateString();
                $rows[] = [
                    'user_id' => $uid,
                    'shift_id' => $data['shift_id'],
                    'work_date' => $workDate,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }
        }

        $overwrite = (bool) ($data['overwrite'] ?? false);

        DB::transaction(function () use ($rows, $overwrite) {
            if ($overwrite) {
                foreach (array_chunk($rows, 1000) as $chunk) {
                    try {
                        AttendanceShiftWork::upsert($chunk, ['user_id', 'work_date'], ['shift_id', 'updated_at']);
                    } catch (\Throwable $e) {
                        $pairs = collect($chunk)->map(function ($r) {
                            return [$r['user_id'], $r['work_date']];
                        })->all();
                        foreach ($pairs as [$u, $d]) {
                            AttendanceShiftWork::where('user_id', $u)->where('work_date', $d)->delete();
                        }
                        AttendanceShiftWork::insert($chunk);
                    }
                }
            } else {
                $byUser = collect($rows)->groupBy('user_id');
                foreach ($byUser as $uid => $items) {
                    $dates = $items->pluck('work_date')->unique()->values();
                    $existing = AttendanceShiftWork::where('user_id', $uid)
                        ->whereIn('work_date', $dates)
                        ->pluck('work_date')
                        ->all();

                    $toInsert = $items->reject(function ($r) use ($existing) {
                        return in_array($r['work_date'], $existing, true);
                    })->values()->all();

                    if (!empty($toInsert)) {
                        foreach (array_chunk($toInsert, 1000) as $chunk) {
                            AttendanceShiftWork::insert($chunk);
                        }
                    }
                }
            }
        });

        return back()->with('success', 'Penugasan shift berhasil diproses.');
    }

    public function update(Request $request, $attendanceShiftWorkId)
    {
        // Hak akses: edit
        abort_unless(Gate::allows('work_shifts.edit'), 403, 'Anda tidak memiliki akses untuk mengubah shift kerja');

        $attendanceShiftWork = AttendanceShiftWork::find($attendanceShiftWorkId);

        if (!$attendanceShiftWork) {
            return redirect()->route('shift-works.index')->with('error', 'Data tidak ditemukan');
        }

        $request->validate([
            'shift_id' => 'required|exists:shifts,id',
            'work_date' => 'required|date',
        ]);

        $duplicate = AttendanceShiftWork::where('employee_id', $attendanceShiftWork->employee_id)
            ->where('work_date', $request->input('work_date'))
            ->where('id', '!=', $attendanceShiftWorkId)
            ->exists();

        if ($duplicate) {
            return redirect()->route('shift-works.index')->with('error', 'Data shift kerja untuk karyawan ini pada tanggal tersebut sudah ada. Tidak dapat menyimpan duplikasi.');
        }

        $attendanceShiftWork->update($request->only('shift_id', 'work_date'));

        return redirect()->route('shift-works.index')->with('success', 'Data berhasil diperbarui');
    }

    public function show(AttendanceShiftWork $attendanceShiftWork)
    {
        // Hak akses: lihat detail
        abort_unless(Gate::allows('work_shifts.view'), 403, 'Anda tidak memiliki akses untuk melihat detail shift kerja');

        $attendanceShiftWork->load(['employee:id,name,department_id,branch_id', 'employee.department:id,name', 'employee.branch:id,name', 'shift:id,name,start_time,end_time,late_tolerance']);

        return Inertia::render('Admin/ShiftWorks/Show', [
            'shiftWork' => [
                'id' => $attendanceShiftWork->id,
                'user' => [
                    'id' => $attendanceShiftWork->employee?->id,
                    'name' => $attendanceShiftWork->employee?->name,
                ],
                'department' => $attendanceShiftWork->employee->department
                    ? ['id' => $attendanceShiftWork->employee->department->id, 'name' => $attendanceShiftWork->employee->department->name]
                    : null,
                'shift' => $attendanceShiftWork->shift
                    ? [
                        'id' => $attendanceShiftWork->shift->id,
                        'name' => $attendanceShiftWork->shift->name,
                        'start_time' => Carbon::parse($attendanceShiftWork->shift->start_time)->format('H:i'),
                        'end_time' => Carbon::parse($attendanceShiftWork->shift->end_time)->format('H:i'),
                        'late_tolerance' => $attendanceShiftWork->shift->late_tolerance,
                    ]
                    : null,
                'work_date' => $attendanceShiftWork->work_date,
                'created_at' => $attendanceShiftWork->created_at?->format('Y-m-d H:i:s'),
            ],
        ]);
    }

    public function destroy(AttendanceShiftWork $attendanceShiftWork)
    {
        // Hak akses: hapus
        abort_unless(Gate::allows('work_shifts.delete'), 403, 'Anda tidak memiliki akses untuk menghapus shift kerja');

        // Validasi: cek apakah sudah ada data absensi terkait
        $hasAttendance = Attendance::where('employee_id', $attendanceShiftWork->employee_id)
            ->where('shift_id', $attendanceShiftWork->shift_id)
            ->exists();

        if ($hasAttendance) {
            return back()->with('error', 'Tidak dapat menghapus data shift kerja karena sudah ada data absensi pada tanggal tersebut.');
        }

        try {
            $attendanceShiftWork->delete();
            return back()->with('success', 'Data berhasil dihapus.');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal menghapus data!');
        }
    }

    public function bulkDestroy(Request $request)
    {
        // Hak akses: hapus
        abort_unless(Gate::allows('work_shifts.delete'), 403, 'Anda tidak memiliki akses untuk menghapus shift kerja');

        $request->validate([
            'ids' => 'required|array|min:1',
            'ids.*' => 'required|integer',
        ]);

        $ids = $request->input('ids');
        $deletedCount = 0;
        $failedCount = 0;
        $errors = [];

        foreach ($ids as $id) {
            try {
                $shiftWork = AttendanceShiftWork::find($id);

                if (!$shiftWork) {
                    $failedCount++;
                    $errors[] = "Data ID {$id} tidak ditemukan.";
                    continue;
                }

                // Validasi: cek apakah sudah ada data absensi terkait
                $hasAttendance = Attendance::where('employee_id', $shiftWork->employee_id)
                    ->where('shift_id', $shiftWork->shift_id)
                    ->exists();

                if ($hasAttendance) {
                    $failedCount++;
                    $errors[] = "Data ID {$id} tidak dapat dihapus karena sudah ada data absensi pada tanggal tersebut.";
                    continue;
                }

                $shiftWork->delete();
                $deletedCount++;
            } catch (\Exception $e) {
                $failedCount++;
                $errors[] = "Gagal menghapus data ID {$id}: " . $e->getMessage();
            }
        }

        if ($failedCount > 0 && $deletedCount > 0) {
            return back()->with('warning', "Berhasil menghapus {$deletedCount} data. {$failedCount} data gagal dihapus karena sudah ada data absensi pada tanggal tersebut.");
        } elseif ($failedCount > 0) {
            return back()->with('error', "Gagal menghapus {$failedCount} data.  karena sudah ada data absensi pada tanggal tersebut.");
        }

        return back()->with('success', "Berhasil menghapus {$deletedCount} data.");
    }

    public function bulkUpdate(Request $request)
    {
        // Hak akses: edit
        abort_unless(Gate::allows('work_shifts.edit'), 403, 'Anda tidak memiliki akses untuk mengubah shift kerja');

        $request->validate([
            'ids' => 'required|array|min:1',
            'ids.*' => 'required|integer',
            'shift_id' => 'required|exists:shifts,id',
        ]);

        $ids = $request->input('ids');
        $shiftId = $request->input('shift_id');
        $updatedCount = 0;
        $failedCount = 0;
        $errors = [];

        foreach ($ids as $id) {
            try {
                $shiftWork = AttendanceShiftWork::find($id);

                if (!$shiftWork) {
                    $failedCount++;
                    $errors[] = "Data ID {$id} tidak ditemukan.";
                    continue;
                }

                $shiftWork->update(['shift_id' => $shiftId]);
                $updatedCount++;
            } catch (\Exception $e) {
                $failedCount++;
                $errors[] = "Gagal mengubah data ID {$id}: " . $e->getMessage();
            }
        }

        if ($failedCount > 0 && $updatedCount > 0) {
            return back()->with('warning', "Berhasil mengubah {$updatedCount} data. {$failedCount} data gagal diubah.");
        } elseif ($failedCount > 0) {
            return back()->with('error', "Gagal mengubah {$failedCount} data.");
        }

        return back()->with('success', "Berhasil mengubah shift untuk {$updatedCount} data.");
    }
}
