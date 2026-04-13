<?php

namespace App\Actions\Data\Dashboard;

use App\Models\Attendance;
use App\Models\Employee;
use Carbon\Carbon;

class GetKaryawanTodayActivities
{
    public function execute()
    {
        $today = Carbon::today();

        $activities = Attendance::with('employee')
            ->whereDate('tanggal', $today)
            ->whereNotNull('jam_masuk')
            ->orderBy('jam_masuk', 'asc')
            ->get()
            ->map(function ($attendance) {
                $isLate = $attendance->jam_masuk && Carbon::parse($attendance->jam_masuk)->format('H:i') > '08:00';

                return [
                    'id' => $attendance->id,
                    'name' => $attendance->employee->name ?? 'Unknown',
                    'time' => $attendance->jam_masuk ? Carbon::parse($attendance->jam_masuk)->format('H:i') : 'N/A',
                    'status' => $isLate ? 'Telat' : 'Hadir'
                ];
            });

        return $activities->toArray();
    }
}
