<?php

namespace App\Actions\Data\Dashboard;

use App\Models\Employee;
use App\Models\Attendance;
use App\Models\EmployeeLeaveRequest;
use Carbon\Carbon;

class GetKaryawanMetrics
{
    public function execute()
    {
        $today = Carbon::today();

        // Total karyawan - all employees
        $totalKaryawan = Employee::count();

        // Karyawan aktif - employees who have attendance today
        $karyawanAktif = Attendance::whereDate('tanggal', $today)
            ->whereNotNull('jam_masuk')
            ->distinct('employee_id')
            ->count();

        // Karyawan telat - employees who are late today
        // Assuming late means jam_masuk is after 08:00 (this can be adjusted based on shift)
        $karyawanTelat = Attendance::whereDate('tanggal', $today)
            ->whereNotNull('jam_masuk')
            ->whereTime('jam_masuk', '>', '08:00:00')
            ->distinct('employee_id')
            ->count();

        // Karyawan cuti - employees on leave today
        $karyawanCuti = EmployeeLeaveRequest::where('status', 'approved')
            ->where('start_date', '<=', $today)
            ->where('end_date', '>=', $today)
            ->distinct('employee_id')
            ->count();

        // Kinerja karyawan - percentage of employees who are on time
        $totalAttendanceToday = Attendance::whereDate('tanggal', $today)
            ->whereNotNull('jam_masuk')
            ->distinct('employee_id')
            ->count();

        $onTimeToday = Attendance::whereDate('tanggal', $today)
            ->whereNotNull('jam_masuk')
            ->whereTime('jam_masuk', '<=', '08:00:00')
            ->distinct('employee_id')
            ->count();

        $kinerjaKaryawan = $totalAttendanceToday > 0
            ? round(($onTimeToday / $totalAttendanceToday) * 100, 1)
            : 0;

        return [
            'total_karyawan' => $totalKaryawan,
            'active_karyawan' => $karyawanAktif,
            'late_karyawan' => $karyawanTelat,
            'on_leave' => $karyawanCuti,
            'performance' => $kinerjaKaryawan . '%'
        ];
    }
}
