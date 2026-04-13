<?php

namespace App\Actions\Data\Dashboard;

use App\Models\Employee;
use Carbon\Carbon;

class GetExemplaryEmployees
{
    public function execute($limit = 4)
    {
        // Get employees with best attendance records (most on-time arrivals in the last 30 days)
        $thirtyDaysAgo = Carbon::now()->subDays(30);

        $exemplaryEmployees = Employee::with(['department', 'attendances' => function ($query) use ($thirtyDaysAgo) {
            $query->where('tanggal', '>=', $thirtyDaysAgo)
                ->whereNotNull('jam_masuk');
        }])
            ->get()
            ->map(function ($employee) {
                $onTimeCount = $employee->attendances->filter(function ($attendance) {
                    return $attendance->jam_masuk &&
                        Carbon::parse($attendance->jam_masuk)->format('H:i') <= '08:00';
                })->count();

                $totalAttendance = $employee->attendances->count();
                $onTimePercentage = $totalAttendance > 0 ? ($onTimeCount / $totalAttendance) * 100 : 0;

                return [
                    'id' => $employee->id,
                    'name' => $employee->name,
                    'department' => $employee->department->name ?? 'Unknown',
                    'on_time_percentage' => $onTimePercentage,
                    'total_attendance' => $totalAttendance,
                    'time' => round($onTimePercentage) . '%' // standardized for UI
                ];
            })
            ->filter(function ($employee) {
                return $employee['total_attendance'] > 0; // Only employees with some attendance
            })
            ->sortByDesc('on_time_percentage')
            ->take($limit)
            ->values()
            ->map(function ($employee) {
                return [
                    'id' => $employee['id'],
                    'name' => $employee['name'],
                    'department' => $employee['department'],
                    'time' => $employee['time']
                ];
            });

        return $exemplaryEmployees->toArray();
    }
}
