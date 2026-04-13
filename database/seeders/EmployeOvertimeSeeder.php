<?php

namespace Database\Seeders;

use App\Models\Employee;
use App\Models\EmployeeOvertime;
use App\Enums\EmployeeOvertimeStatusEnum;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class EmployeOvertimeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get all employees
        $employees = Employee::all();

        if ($employees->isEmpty()) {
            $this->command->warn('No employees found. Please run EmployeeSeeder first.');
            return;
        }

        $overtimeReasons = [
            'Penyelesaian proyek mendesak',
            'Pemrosesan data bulanan',
            'Persiapan laporan keuangan',
            'Maintenance sistem IT',
            'Penyelesaian order customer',
            'Persiapan presentasi manajemen',
            'Backup data dan sistem',
            'Penyelesaian dokumen kontrak',
            'Persiapan audit internal',
            'Penyelesaian laporan bulanan',
            'Maintenance peralatan produksi',
            'Penyelesaian proyek client',
            'Persiapan training karyawan',
            'Penyelesaian laporan keuangan',
            'Maintenance database',
            'Penyelesaian dokumen legal',
            'Persiapan meeting board',
            'Penyelesaian laporan operasional',
            'Maintenance server',
            'Penyelesaian proyek marketing'
        ];

        $statuses = EmployeeOvertimeStatusEnum::cases();
        $statusWeights = [
            EmployeeOvertimeStatusEnum::PENDING->value => 0.3,  // 30% pending
            EmployeeOvertimeStatusEnum::APPROVED->value => 0.6, // 60% approved
            EmployeeOvertimeStatusEnum::REJECTED->value => 0.1, // 10% rejected
        ];

        // Create overtime records for the last 3 months
        $startDate = Carbon::now()->subMonths(3)->startOfMonth();
        $endDate = Carbon::now()->endOfMonth();

        for ($date = $startDate->copy(); $date->lte($endDate); $date->addDay()) {
            // Skip weekends for most overtime (but allow some weekend overtime)
            if ($date->isWeekend() && rand(1, 10) > 2) {
                continue;
            }

            // Randomly decide if there's overtime on this day (30% chance)
            if (rand(1, 10) <= 3) {
                // Select random employees for overtime (1-5 employees per day)
                $overtimeCount = rand(1, min(5, $employees->count()));
                $selectedEmployees = $employees->random($overtimeCount);

                foreach ($selectedEmployees as $employee) {
                    // Generate realistic overtime hours (1-4 hours)
                    $durationHours = rand(1, 4);

                    // Generate start time (after 17:00 for regular overtime)
                    $startHour = 17 + rand(0, 2); // 17:00 - 19:00
                    $startMinute = rand(0, 59);
                    $startTime = Carbon::createFromTime($startHour, $startMinute);

                    // Calculate end time based on duration
                    $endTime = $startTime->copy()->addHours($durationHours);

                    // Ensure end time doesn't go past midnight
                    if ($endTime->hour >= 24) {
                        $endTime = Carbon::createFromTime(23, 59);
                        $durationHours = $startTime->diffInHours($endTime);
                    }

                    // Select status based on weights
                    $randomStatus = rand(1, 100);
                    $cumulativeWeight = 0;
                    $selectedStatus = EmployeeOvertimeStatusEnum::PENDING;

                    foreach ($statusWeights as $status => $weight) {
                        $cumulativeWeight += $weight * 100;
                        if ($randomStatus <= $cumulativeWeight) {
                            $selectedStatus = EmployeeOvertimeStatusEnum::from($status);
                            break;
                        }
                    }

                    // Select approver for approved/rejected overtime
                    $approvedBy = null;
                    if ($selectedStatus !== EmployeeOvertimeStatusEnum::PENDING) {
                        $approvers = $employees->where('id', '!=', $employee->id);
                        if ($approvers->isNotEmpty()) {
                            $approvedBy = $approvers->random()->id;
                        }
                    }

                    // Generate notes for approved/rejected overtime
                    $notes = null;
                    if ($selectedStatus === EmployeeOvertimeStatusEnum::APPROVED) {
                        $approvalNotes = [
                            'Overtime disetujui untuk penyelesaian proyek',
                            'Lembur diperlukan untuk deadline yang ketat',
                            'Overtime disetujui sesuai kebutuhan operasional',
                            'Lembur disetujui untuk maintenance sistem',
                            'Overtime diperlukan untuk laporan bulanan'
                        ];
                        $notes = $approvalNotes[array_rand($approvalNotes)];
                    } elseif ($selectedStatus === EmployeeOvertimeStatusEnum::REJECTED) {
                        $rejectionNotes = [
                            'Overtime tidak diperlukan, dapat diselesaikan esok hari',
                            'Lembur ditolak karena tidak ada urgensi',
                            'Overtime tidak sesuai dengan kebijakan perusahaan',
                            'Lembur ditolak karena alasan budget',
                            'Overtime tidak disetujui karena alasan operasional'
                        ];
                        $notes = $rejectionNotes[array_rand($rejectionNotes)];
                    }

                    EmployeeOvertime::create([
                        'employee_id' => $employee->id,
                        'date' => $date->format('Y-m-d'),
                        'start_time' => $startTime->format('H:i:s'),
                        'end_time' => $endTime->format('H:i:s'),
                        'duration_hours' => $durationHours,
                        'reason' => $overtimeReasons[array_rand($overtimeReasons)],
                        'status' => $selectedStatus,
                        'approved_by' => $approvedBy,
                        'notes' => $notes,
                        'created_at' => $date->copy()->addHours(rand(8, 18))->addMinutes(rand(0, 59)),
                        'updated_at' => $date->copy()->addHours(rand(8, 18))->addMinutes(rand(0, 59)),
                    ]);
                }
            }
        }

        $this->command->info('EmployeeOvertime seeder completed successfully!');
        $this->command->info('Created overtime records for the last 3 months.');
    }
}
