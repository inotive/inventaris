<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Attendance;
use App\Models\Employee;
use App\Models\Department;
use App\Models\Shift;

class AttendanceHistorySeeder extends Seeder
{
    public function run(): void
    {
        $employees = Employee::select('id')->limit(20)->get();
        if ($employees->isEmpty()) {
            return;
        }

        $departments = Department::select('id')->get();
        $statusPool = ['RUNNING', 'COMPLETE', 'CUTI', 'ABSEN'];
        $shifts = Shift::select('id')->get();

        $now = now();
        $rows = [];

        foreach ($employees as $employee) {
            for ($i = 0; $i < 14; $i++) { // 2 minggu terakhir
                $date = $now->copy()->startOfDay()->subDays($i)->toDateString();

                // Randomize presence
                $status = $statusPool[array_rand($statusPool)];
                $jamMasuk = null;
                $jamKeluar = null;

                if ($status === 'RUNNING') {
                    // Hanya isi jam_masuk saja
                    $startHour = 8 + rand(0, 1); // 08 or 09
                    $startMin = [0, 5, 10, 15, 20, 30, 45][array_rand([0, 1, 2, 3, 4, 5, 6])];
                    $jamMasuk = sprintf('%02d:%02d:00', $startHour, $startMin);
                    $jamKeluar = null;
                } elseif ($status === 'COMPLETE') {
                    // Isi jam_masuk dan jam_keluar
                    $startHour = 8 + rand(0, 1); // 08 or 09
                    $startMin = [0, 5, 10, 15, 20, 30, 45][array_rand([0, 1, 2, 3, 4, 5, 6])];
                    $jamMasuk = sprintf('%02d:%02d:00', $startHour, $startMin);

                    $endHour = 17 + rand(0, 1); // 17 or 18
                    $endMin = [0, 5, 10, 15, 20, 30][array_rand([0, 1, 2, 3, 4, 5])];
                    // leave earlier by up to 2 hours
                    $endHour = max(15, $endHour - rand(0, 2));
                    $jamKeluar = sprintf('%02d:%02d:00', $endHour, $endMin);
                } elseif ($status === 'CUTI') {
                    // Tidak isi jam_masuk dan jam_keluar
                    $jamMasuk = null;
                    $jamKeluar = null;
                } elseif ($status === 'ABSEN') {
                    // Tidak isi jam_masuk dan jam_keluar
                    $jamMasuk = null;
                    $jamKeluar = null;
                }

                $rows[] = [
                    'employee_id' => $employee->id,
                    'department_id' => $departments->random()->id ?? null,
                    'tanggal' => $date,
                    'shift_id' => $shifts->random()->id ?? null,
                    'jam_masuk' => $jamMasuk,
                    'jam_keluar' => $jamKeluar,
                    'status' => $status,
                    'keterangan' => null,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];

                if (count($rows) >= 1000) {
                    Attendance::insert($rows);
                    $rows = [];
                }
            }
        }

        if (!empty($rows)) {
            Attendance::insert($rows);
        }
    }
}
