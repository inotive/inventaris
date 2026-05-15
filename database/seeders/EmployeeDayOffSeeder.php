<?php

namespace Database\Seeders;

use App\Models\Employee;
use App\Models\Department;
use App\Models\EmployeeDayOff;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class EmployeeDayOffSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Kosongkan data lama
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        EmployeeDayOff::truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $employees = Employee::with('department')->get();
        $departments = Department::all();

        if ($employees->isEmpty() || $departments->isEmpty()) {
            if (method_exists($this, 'command') && $this->command) {
                $this->command->warn('No employees or departments found. Please run EmployeeSeeder and DepartmentSeeder first.');
            }
            return;
        }

        $dayOffReasons = [
            'Cuti Tahunan',
            'Cuti Sakit',
            'Cuti Melahirkan',
            'Cuti Keluarga',
            'Cuti Khusus',
            'Izin Pribadi',
            'Izin Keluarga',
            'Izin Kesehatan',
            'Izin Keagamaan',
            'Izin Pendidikan',
            'Izin Khusus',
            'Cuti Darurat',
            'Izin Darurat',
            'Cuti Bersama',
            'Izin Resmi'
        ];

        $now = now();
        $startDate = Carbon::now()->subMonths(3);
        $endDate = Carbon::now()->addMonths(3);

        $dayOffRecords = [];
        $usedDates = [];

        // Generate day-off records untuk setiap karyawan
        foreach ($employees as $employee) {
            $numberOfDayOffs = rand(1, 8);
            $employeeUsedDates = [];

            for ($i = 0; $i < $numberOfDayOffs; $i++) {
                $attempts = 0;
                $maxAttempts = 50;
                $randomDate = null;

                do {
                    $randomDate = Carbon::createFromTimestamp(
                        rand($startDate->timestamp, $endDate->timestamp)
                    );

                    // 80% kemungkinan skip weekend
                    if (rand(1, 100) <= 80 && $randomDate->isWeekend()) {
                        $randomDate = $randomDate->next(Carbon::MONDAY);
                    }

                    if ($randomDate->lt($startDate) || $randomDate->gt($endDate)) {
                        $attempts++;
                        continue;
                    }

                    $dateString = $randomDate->toDateString();
                    $uniqueKey = $employee->id . '-' . $dateString;

                    if (in_array($uniqueKey, $usedDates) || in_array($dateString, $employeeUsedDates)) {
                        $attempts++;
                        continue;
                    }

                    break;
                } while ($attempts < $maxAttempts);

                if ($attempts >= $maxAttempts) {
                    continue;
                }

                $dateString = $randomDate->toDateString();
                $uniqueKey = $employee->id . '-' . $dateString;

                $usedDates[] = $uniqueKey;
                $employeeUsedDates[] = $dateString;

                $dayOffRecords[] = [
                    'employee_id' => $employee->id,
                    'date' => $dateString,
                    'name' => $dayOffReasons[array_rand($dayOffReasons)],
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }
        }

        // Insert day-off records
        if (!empty($dayOffRecords)) {
            foreach ($dayOffRecords as $record) {
                EmployeeDayOff::updateOrCreate(
                    [
                        'employee_id' => $record['employee_id'],
                        'date'        => $record['date']
                    ],
                    $record
                );
            }
            if (method_exists($this, 'command') && $this->command) {
                $this->command->info('Created ' . count($dayOffRecords) . ' employee day-off records.');
            }
        }

        // Event perusahaan (department-wide day-off)
        $companyEvents = [
            'Hari Raya Idul Fitri',
            'Hari Raya Idul Adha',
            'Tahun Baru',
            'Hari Kemerdekaan',
            'Hari Buruh',
            'Hari Kartini',
            'Hari Pahlawan',
            'Libur Nasional',
            'Cuti Bersama',
            'Acara Perusahaan',
            'Pelatihan Karyawan',
            'Rapat Perusahaan',
            'Maintenance Sistem',
            'Libur Akhir Tahun',
            'Hari Libur Khusus'
        ];

        $departmentDayOffs = [];
        $companyEventDates = [
            Carbon::now()->subMonths(2)->addDays(rand(1, 15)),
            Carbon::now()->subMonth()->addDays(rand(1, 15)),
            Carbon::now()->addDays(rand(1, 15)),
            Carbon::now()->addMonth()->addDays(rand(1, 15)),
            Carbon::now()->addMonths(2)->addDays(rand(1, 15)),
        ];

        foreach ($companyEventDates as $eventDate) {
            if ($eventDate->lt($startDate) || $eventDate->gt($endDate)) {
                continue;
            }

            foreach ($employees as $employee) {
                if (rand(1, 100) <= 70) {
                    $dateString = $eventDate->toDateString();
                    $uniqueKey = $employee->id . '-' . $dateString;

                    if (in_array($uniqueKey, $usedDates)) {
                        continue;
                    }

                    $usedDates[] = $uniqueKey;

                    $departmentDayOffs[] = [
                        'employee_id' => $employee->id,
                        'date' => $dateString,
                        'name' => $companyEvents[array_rand($companyEvents)],
                        'created_at' => $now,
                        'updated_at' => $now,
                    ];
                }
            }
        }

        if (!empty($departmentDayOffs)) {
            foreach ($departmentDayOffs as $record) {
                EmployeeDayOff::updateOrCreate(
                    [
                        'employee_id' => $record['employee_id'],
                        'date'        => $record['date']
                    ],
                    $record
                );
            }
            if (method_exists($this, 'command') && $this->command) {
                $this->command->info('Created ' . count($departmentDayOffs) . ' department-wide day-off records.');
            }
        }

        $totalRecords = count($dayOffRecords) + count($departmentDayOffs);
        if (method_exists($this, 'command') && $this->command) {
            $this->command->info("Total employee day-off records created: {$totalRecords}");
        }
    }
}
