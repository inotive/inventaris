<?php

namespace Database\Seeders;

use App\Models\DailyReport;
use App\Models\Employee;
use Faker\Factory as Faker;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class DailyReportSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faker = Faker::create('id_ID');

        // Get existing employees
        $employees = Employee::all();
        if ($employees->isEmpty()) {
            $this->command->warn('No employees found. Please run EmployeeSeeder first.');
            return;
        }

        // Get some employees to act as approvers (managers/supervisors)
        $approvers = $employees->where('status', 'Tetap')->take(5);

        // Create daily reports for the last 30 days
        for ($i = 0; $i < 30; $i++) {
            $reportDate = Carbon::now()->subDays($i);
            $startDate = $reportDate->copy()->setTime(8, 0, 0);
            $endDate = $reportDate->copy()->setTime(17, 0, 0);

            // Create 3-8 daily reports per day
            $reportCount = $faker->numberBetween(3, 8);

            for ($j = 0; $j < $reportCount; $j++) {
                $employee = $employees->random();
                $status = $faker->randomElement(['pending', 'approved', 'rejected']);
                $approvedBy = null;
                $approvedAt = null;

                // If approved or rejected, assign approver and approval date
                if (in_array($status, ['approved', 'rejected'])) {
                    $approvedBy = $approvers->random();
                    $approvedAt = $faker->dateTimeBetween($startDate, $startDate->copy()->addDays(2));
                }

                // Generate realistic reasons for daily reports
                $reasons = [
                    'Laporan kerja harian operasional',
                    'Laporan produksi harian',
                    'Laporan keamanan dan kebersihan',
                    'Laporan maintenance peralatan',
                    'Laporan kualitas produk',
                    'Laporan kehadiran tim',
                    'Laporan inspeksi area kerja',
                    'Laporan pengiriman barang',
                    'Laporan penerimaan material',
                    'Laporan keuangan harian',
                    'Laporan customer service',
                    'Laporan IT support',
                    'Laporan HR harian',
                    'Laporan warehouse',
                    'Laporan quality control'
                ];

                // Generate realistic notes
                $notes = [
                    'Semua target harian tercapai dengan baik',
                    'Ada beberapa kendala teknis yang sudah diatasi',
                    'Perlu perhatian khusus untuk area produksi',
                    'Tim bekerja dengan efisien dan disiplin',
                    'Ada peningkatan performa dibanding hari sebelumnya',
                    'Memerlukan koordinasi lebih lanjut dengan departemen lain',
                    'Semua SOP sudah dijalankan dengan benar',
                    'Ada beberapa catatan penting untuk evaluasi',
                    'Kondisi peralatan dalam keadaan baik',
                    'Tidak ada kejadian yang merugikan'
                ];

                DailyReport::create([
                    'employee_id' => $employee->id,
                    'start_date' => $startDate,
                    'end_date' => $endDate,
                    'count_employee' => $faker->numberBetween(1, 15), // Number of employees involved
                    'salary' => $faker->numberBetween(50000, 200000), // Daily salary amount
                    'reason' => $faker->randomElement($reasons),
                    'status' => $status,
                    'approved_by' => $approvedBy?->id,
                    'approved_at' => $approvedAt,
                    'notes' => $faker->randomElement($notes),
                ]);
            }
        }

        // Create some special cases
        $this->createSpecialCases($employees, $approvers, $faker);

        $this->command->info('Daily reports seeded successfully!');
    }

    private function createSpecialCases($employees, $approvers, $faker)
    {
        // Create some urgent reports
        for ($i = 0; $i < 5; $i++) {
            $employee = $employees->random();
            $urgentDate = Carbon::now()->subDays($faker->numberBetween(1, 7));

            DailyReport::create([
                'employee_id' => $employee->id,
                'start_date' => $urgentDate->copy()->setTime(6, 0, 0),
                'end_date' => $urgentDate->copy()->setTime(22, 0, 0),
                'count_employee' => $faker->numberBetween(5, 20),
                'salary' => $faker->numberBetween(100000, 300000),
                'reason' => 'Laporan darurat - ' . $faker->randomElement([
                    'Kecelakaan kerja',
                    'Kerusakan peralatan kritis',
                    'Masalah keamanan',
                    'Insiden kualitas produk',
                    'Gangguan sistem'
                ]),
                'status' => 'approved',
                'approved_by' => $approvers->random()->id,
                'approved_at' => $urgentDate->copy()->addHours(2),
                'notes' => 'Laporan darurat yang memerlukan penanganan segera. Semua tindakan sudah diambil sesuai prosedur.',
            ]);
        }

        // Create some weekend reports
        for ($i = 0; $i < 3; $i++) {
            $employee = $employees->random();
            $weekendDate = Carbon::now()->previous(Carbon::SATURDAY)->addDays($i);

            DailyReport::create([
                'employee_id' => $employee->id,
                'start_date' => $weekendDate->copy()->setTime(9, 0, 0),
                'end_date' => $weekendDate->copy()->setTime(15, 0, 0),
                'count_employee' => $faker->numberBetween(2, 8),
                'salary' => $faker->numberBetween(75000, 150000),
                'reason' => 'Laporan kerja weekend - ' . $faker->randomElement([
                    'Maintenance terjadwal',
                    'Persiapan produksi minggu depan',
                    'Inventarisasi barang',
                    'Pembersihan area kerja',
                    'Backup data sistem'
                ]),
                'status' => $faker->randomElement(['pending', 'approved']),
                'approved_by' => $faker->randomElement([null, $approvers->random()->id]),
                'approved_at' => $faker->randomElement([null, $weekendDate->copy()->addDays(1)]),
                'notes' => 'Kerja weekend untuk ' . $faker->randomElement([
                    'menyelesaikan backlog',
                    'persiapan proyek baru',
                    'maintenance preventif',
                    'pelatihan tim'
                ]),
            ]);
        }
    }
}
