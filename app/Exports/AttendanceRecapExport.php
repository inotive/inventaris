<?php

namespace App\Exports;

use App\Models\Employee;
use App\Models\Attendance;
use Carbon\Carbon;

class AttendanceRecapExport
{
    protected $year, $month, $departmentId, $employeeId, $branchId, $search, $status;

    public function __construct($year, $month, $departmentId = null, $employeeId = null, $branchId = null, $search = null, $status = null)
    {
        $this->year = $year;
        $this->month = $month;
        $this->departmentId = $departmentId;
        $this->employeeId = $employeeId;
        $this->branchId = $branchId;
        $this->search = $search;
        $this->status = $status;
    }

    public function download()
    {
        $start = Carbon::createFromDate($this->year, $this->month, 1)->startOfDay();
        $end = (clone $start)->endOfMonth();
        $daysInMonth = $end->day;

        // Get employee data
        $employees = $this->getEmployeeData($start, $end, $daysInMonth);

        $data = [
            'year' => $this->year,
            'month' => $this->month,
            'monthName' => $start->translatedFormat('F'),
            'daysInMonth' => $daysInMonth,
            'employees' => $employees,
        ];

        $filename = "Rekap_Kehadiran_{$this->year}-" . str_pad($this->month, 2, '0', STR_PAD_LEFT) . ".xls";

        return response()->view('exports.attendance-recap-monthly', $data, 200, [
            'Content-Type' => 'application/vnd.ms-excel',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }

    private function getEmployeeData($start, $end, $daysInMonth)
    {
        $employees = Employee::query()
            ->select(['employees.id', 'employees.name', 'employees.department_id', 'employees.branch_id', 'employees.position_id'])
            ->with(['department:id,name', 'branch:id,name', 'position:id,name'])
            ->when($this->search, function ($q) {
                return $q->where('employees.name', 'like', "%{$this->search}%");
            })
            ->when($this->departmentId, function ($q) {
                return $q->where('employees.department_id', $this->departmentId);
            })
            ->when($this->employeeId, function ($q) {
                return $q->where('employees.id', $this->employeeId);
            })
            ->when($this->branchId, function ($q) {
                return $q->where('employees.branch_id', $this->branchId);
            })
            ->when($this->status, function ($q) {
                return $q->whereHas('user', function ($u) {
                    $u->where('status', $this->status);
                });
            })
            ->orderBy('employees.name')
            ->get();

        $result = [];
        foreach ($employees as $emp) {
            $attendances = Attendance::where('employee_id', $emp->id)
                ->whereBetween('tanggal', [$start->toDateString(), $end->toDateString()])
                ->get()
                ->keyBy(function ($a) {
                    return (int) date('j', strtotime($a->tanggal));
                });

            $days = [];
            $recap = ['H' => 0, 'T' => 0, 'A' => 0, 'C' => 0, 'S' => 0, 'I' => 0];

            for ($d = 1; $d <= $daysInMonth; $d++) {
                $att = $attendances->get($d);
                $status = $att ? $this->mapStatusToCode($att->status) : '-';
                $days[$d] = $status;

                if (isset($recap[$status])) {
                    $recap[$status]++;
                }
            }

            $result[] = [
                'name' => $emp->name,
                'department' => $emp->department->name ?? '-',
                'position' => $emp->position->name ?? '-',
                'branch' => $emp->branch->name ?? '-',
                'days' => $days,
                'recap' => $recap,
            ];
        }

        return $result;
    }

    private function mapStatusToCode(?string $status)
    {
        if (!$status) {
            return '-';
        }

        $s = strtoupper($status);

        // Hadir / On Time / Complete / Running
        if (strpos($s, 'ON TIME') !== false) {
            return 'H';
        }
        if (strpos($s, 'COMPLETE') !== false) {
            return 'H';
        }
        if (strpos($s, 'RUNNING') !== false) {
            // Running juga dihitung sebagai hadir
            return 'H';
        }
        if ($s === 'HADIR') {
            return 'H';
        }

        // Terlambat
        if (strpos($s, 'TERLAMBAT') !== false) {
            return 'T';
        }

        // Alpa / Absen
        if (strpos($s, 'ALPA') !== false || strpos($s, 'ALPHA') !== false || strpos($s, 'ABSEN') !== false) {
            return 'A';
        }

        // Cuti
        if (strpos($s, 'CUTI') !== false) {
            return 'C';
        }

        // Sakit
        if (strpos($s, 'SAKIT') !== false) {
            return 'S';
        }

        // Izin
        if (strpos($s, 'IZIN') !== false) {
            return 'I';
        }

        // Status lain (misalnya shift belum terjadi, dll.)
        return '-';
    }
}
