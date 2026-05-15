<?php

namespace App\Actions\Data\Dashboard;

use App\Models\Attendance;
use Illuminate\Support\Facades\DB;

class GetAbsensiChartData
{
    public function execute($year)
    {
        // Pastikan model Attendance sudah ada
        $monthlyAttendance = Attendance::selectRaw('MONTH(tanggal) as month, COUNT(*) as total_masuk')
            ->whereYear('tanggal', $year)
            ->where('status', 'masuk')
            ->groupByRaw('MONTH(tanggal)')
            ->orderBy('month')
            ->get();

        // Initialize data for all months
        $chartData = [
            'categories' => ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'],
            'series' => [
                'total_masuk' => array_fill(0, 12, 0)
            ]
        ];

        // Fill in actual data
        foreach ($monthlyAttendance as $data) {
            $monthIndex = $data->month - 1; // Convert to 0-based index
            $chartData['series']['total_masuk'][$monthIndex] = (int) $data->total_masuk;
        }

        return $chartData;
    }
}
