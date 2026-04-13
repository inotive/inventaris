<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Employee;
use App\Models\AttendanceShiftWork;
use App\Models\Branch;
use App\Models\Department;
use App\Models\EmployeeDayOff;
use App\Models\Shift;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CalendarController extends Controller
{
    public function index(Request $request)
    {
        $isSuperadmin = Auth::user()->hasRole("Superadmin");
        // Selected month (YYYY-MM); default to current month
        $month = (string) $request->input('month', now()->format('Y-m'));
        $start = Carbon::createFromFormat('Y-m', $month)->startOfMonth();
        $end = (clone $start)->endOfMonth();
        $daysInMonth = (int) $end->format('d');

        // Filter parameters
        $user = Auth::user();
        $search = $request->input('search');
        $departmentFilter = $request->input('department_id');
        $branchFilter = ($isSuperadmin || Auth::user()->employee->branch_id == 2) ? $request->input('branch_id') : Auth::user()->employee->branch_id;
        // If user status is "active" and no status filter from request, default to "active"
        $statusFilter = $request->input('status') ?: ($user->status === 'active' ? 'active' : null);
        $perPage = (int) $request->input('per_page', 15); // Default 50 per page

        // Basic employee listing for the grid with filters
        $employeeQuery = Employee::query()
            ->select(['id', 'name', 'department_id', 'branch_id', 'status', 'path', 'gender'])
            ->with(['department:id,name', 'branch:id,name'])
            ->orderBy('name');

        // Apply search filter
        if ($search) {
            $employeeQuery->where('name', 'like', "%{$search}%");
        }

        // Apply department filter
        if ($departmentFilter) {
            $employeeQuery->where('department_id', $departmentFilter);
        }

        // Apply branch filter
        if ($branchFilter) {
            $employeeQuery->where('branch_id', $branchFilter);
        }

        // Apply status filter
        if ($statusFilter) {
            $employeeQuery->whereHas('user', function ($query) use ($statusFilter) {
                $query->where('status', $statusFilter);
            });
        }

        // Get paginated employees and keep current filters in pagination links
        $employeesPaginated = $employeeQuery
            ->paginate($perPage)
            ->appends([
                'month' => $month,
                'search' => $search,
                'department_id' => $departmentFilter,
                'branch_id' => $branchFilter,
                'status' => $statusFilter,
                'per_page' => $perPage,
            ]);

        $employees = $employeesPaginated->getCollection()->map(function ($e) use ($start, $end, $daysInMonth) {
            // Build days map from shift assignments
            $assignments = AttendanceShiftWork::with(['shift:id,code'])
                ->where('employee_id', $e->id)
                ->whereBetween('work_date', [$start->toDateString(), $end->toDateString()])
                ->get(['work_date', 'shift_id']);
            // Explicit day-offs
            $dayOffs = EmployeeDayOff::where('employee_id', $e->id)
                ->whereBetween('date', [$start->toDateString(), $end->toDateString()])
                ->pluck('date')
                ->all();

            $days = [];
            for ($d = 1; $d <= $daysInMonth; $d++) {
                $days[$d] = 'L'; // default libur
            }
            foreach ($assignments as $as) {
                $day = (int) date('j', strtotime($as->work_date));
                $days[$day] = optional($as->shift)->code ?: 'L';
            }
            // Override with day-offs (L)
            foreach ($dayOffs as $do) {
                $day = (int) date('j', strtotime($do));
                $days[$day] = 'L';
            }

            // Hitung jumlah hari kerja (yang bukan 'L')
            $workDaysCount = 0;
            $offDaysCount = 0;
            foreach ($days as $status) {
                if ($status !== 'L') {
                    $workDaysCount++;
                } else {
                    $offDaysCount++;
                }
            }

            return [
                'id' => $e->id,
                'name' => $e->name,
                'department' => optional($e->department)->name,
                'department_id' => $e->department_id,
                'branch' => optional($e->branch)->name,
                'branch_id' => $e->branch_id,
                'status' => $e->status,
                'photo_url' => $e->path,
                'gender' => $e->gender,
                'role' => null,
                'days' => $days, // S1/S2/S3 (blue) or L (red)
                'work_days_count' => $workDaysCount, // Jumlah hari kerja
                'off_days_count' => $offDaysCount, // Jumlah hari libur
            ];
        });

        // Replace collection in paginator
        $employeesPaginated->setCollection($employees);

        // Get all shifts for dropdown
        $shifts = Shift::select(['id', 'name', 'code', 'start_time', 'end_time'])
            ->orderBy('name')
            ->get();

        // Get branches and departments for filters
        $branches = Branch::select('id', 'name')->when(!$isSuperadmin && Auth::user()->employee->branch_id != 2, function ($query) {
            return $query->where('id', Auth::user()->employee->branch_id);
        })->orderBy('name')->get();
        $departments = Department::select('id', 'name', 'branch_id')->when(!$isSuperadmin && Auth::user()->employee->branch_id != 2, function ($query) {
            return $query->where('branch_id', Auth::user()->employee->branch_id);
        })->orderBy('name')->get();

        return Inertia::render('Admin/Calendar/Index', [
            'isSuperadmin' => $isSuperadmin,
            'month' => $month,
            'daysInMonth' => $daysInMonth,
            'employees' => $employeesPaginated,
            'shifts' => $shifts,
            'branches' => $branches,
            'departments' => $departments,
            'filters' => [
                'search' => $search,
                'department_id' => $departmentFilter,
                'branch_id' => $branchFilter,
                'status' => $statusFilter,
                'per_page' => $perPage,
            ],
        ]);
    }

    /**
     * Store holidays (libur) for selected employees and dates from modal.
     */
    public function storeHoliday(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            // dates can arrive as comma-separated string or array of 'Y-m-d' or 'd/m/Y'
            'dates' => ['required'],
            'department_id' => ['nullable', 'integer', 'exists:departments,id'],
            'all_employees' => ['sometimes', 'boolean'],
            'employee_ids' => ['nullable', 'array'],
            'employee_ids.*' => ['integer', 'exists:employees,id'],
        ]);

        // Normalize dates to array of Y-m-d
        $datesRaw = $data['dates'];
        if (is_string($datesRaw)) {
            $parts = array_filter(array_map('trim', preg_split('/[,\n]+/', $datesRaw)));
        } else {
            $parts = (array) $datesRaw;
        }
        $dates = [];
        foreach ($parts as $p) {
            $p = trim($p);
            if ($p === '') continue;
            // support d/m/Y or Y-m-d
            try {
                if (preg_match('/^\d{2}\/\d{2}\/\d{4}$/', $p)) {
                    $dates[] = Carbon::createFromFormat('d/m/Y', $p)->format('Y-m-d');
                } else {
                    $dates[] = Carbon::parse($p)->format('Y-m-d');
                }
            } catch (\Throwable $e) {
                // skip invalid date
            }
        }
        $dates = array_values(array_unique($dates));
        if (empty($dates)) {
            return back()->with('error', 'Tanggal libur tidak valid.');
        }

        // Determine target employees
        $all = (bool)($data['all_employees'] ?? false);
        if ($all) {
            $empQuery = Employee::query()->select('id', 'department_id');
            if (!empty($data['department_id'])) {
                $empQuery->where('department_id', $data['department_id']);
            }
            $employees = $empQuery->pluck('id')->all();
        } else {
            $employees = $data['employee_ids'] ?? [];
        }
        if (empty($employees)) {
            return back()->with('error', 'Tidak ada karyawan yang dipilih.');
        }

        // Build rows for upsert
        $now = now();
        $rows = [];
        foreach ($employees as $eid) {
            foreach ($dates as $d) {
                $rows[] = [
                    'employee_id' => $eid,
                    'department_id' => $data['department_id'] ?? null,
                    'date' => $d,
                    'name' => $data['name'],
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }
        }

        DB::transaction(function () use ($rows) {
            // upsert on unique (employee_id, date)
            EmployeeDayOff::upsert($rows, ['employee_id', 'date'], ['name', 'department_id', 'updated_at']);
        });

        return back()->with('success', 'Libur berhasil disimpan.');
    }

    /**
     * Update shift for a specific employee and date
     */
    public function updateShift(Request $request)
    {
        $validated = $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'date' => 'required|date',
            'shift_id' => 'required' // Can be 'L' for Libur or shift ID
        ]);

        $employeeId = $validated['employee_id'];
        $date = $validated['date'];
        $shiftId = $validated['shift_id'];

        DB::beginTransaction();
        try {
            // If shift_id is 'L' (Libur), delete the record
            if ($shiftId === 'L') {
                AttendanceShiftWork::where('employee_id', $employeeId)
                    ->where('work_date', $date)
                    ->delete();

                // Optionally add to EmployeeDayOff table
                EmployeeDayOff::updateOrCreate(
                    [
                        'employee_id' => $employeeId,
                        'date' => $date
                    ],
                    [
                        'name' => 'Libur',
                        'department_id' => null
                    ]
                );
            } else {
                // Remove from day off if exists
                EmployeeDayOff::where('employee_id', $employeeId)
                    ->where('date', $date)
                    ->delete();

                // Insert or update shift work
                AttendanceShiftWork::updateOrCreate(
                    [
                        'employee_id' => $employeeId,
                        'work_date' => $date
                    ],
                    [
                        'shift_id' => $shiftId
                    ]
                );
            }

            DB::commit();
            return back()->with('success', 'Shift berhasil diupdate');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal update shift: ' . $e->getMessage());
        }
    }
}
