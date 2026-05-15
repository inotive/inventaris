<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class LogActivitySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get some users to associate with activities
        $users = \App\Models\User::take(10)->get();

        if ($users->isEmpty()) {
            // Create a default user if none exist
            $user = \App\Models\User::create([
                'name' => 'Admin User',
                'email' => 'admin@henkristal.com',
                'username' => 'admin',
                'password' => bcrypt('password'),
            ]);
            $users = collect([$user]);
        }

        $activities = [
            // Login/Logout Activities
            ['model_type' => 'App\Models\User', 'description' => 'User berhasil login ke sistem'],
            ['model_type' => 'App\Models\User', 'description' => 'User logout dari sistem'],
            ['model_type' => 'App\Models\User', 'description' => 'User mengubah password'],
            ['model_type' => 'App\Models\User', 'description' => 'User mengupdate profil'],

            // Attendance Activities
            ['model_type' => 'App\Models\Attendance', 'description' => 'Karyawan melakukan check-in pagi'],
            ['model_type' => 'App\Models\Attendance', 'description' => 'Karyawan melakukan check-out sore'],
            ['model_type' => 'App\Models\Attendance', 'description' => 'Karyawan terlambat masuk kerja'],
            ['model_type' => 'App\Models\Attendance', 'description' => 'Karyawan pulang lebih awal'],
            ['model_type' => 'App\Models\Attendance', 'description' => 'Karyawan tidak hadir (absen)'],

            // Submission Activities
            ['model_type' => 'App\Models\Submission', 'description' => 'Pengajuan cuti tahunan diajukan'],
            ['model_type' => 'App\Models\Submission', 'description' => 'Pengajuan izin sakit diajukan'],
            ['model_type' => 'App\Models\Submission', 'description' => 'Pengajuan lembur diajukan'],
            ['model_type' => 'App\Models\Submission', 'description' => 'Pengajuan cuti disetujui oleh supervisor'],
            ['model_type' => 'App\Models\Submission', 'description' => 'Pengajuan izin sakit ditolak'],
            ['model_type' => 'App\Models\Submission', 'description' => 'Pengajuan lembur disetujui'],
            ['model_type' => 'App\Models\Submission', 'description' => 'Pengajuan piutang diajukan'],
            ['model_type' => 'App\Models\Submission', 'description' => 'Pengajuan khusus diajukan'],

            // Material Request Activities
            ['model_type' => 'App\Models\MaterialRequest', 'description' => 'Permintaan material baru diajukan'],
            ['model_type' => 'App\Models\MaterialRequest', 'description' => 'Permintaan material disetujui'],
            ['model_type' => 'App\Models\MaterialRequest', 'description' => 'Permintaan material ditolak'],
            ['model_type' => 'App\Models\MaterialRequest', 'description' => 'Material berhasil diterima'],

            // Purchase Activities
            ['model_type' => 'App\Models\PurchaseRequest', 'description' => 'Purchase request baru dibuat'],
            ['model_type' => 'App\Models\PurchaseRequest', 'description' => 'Purchase request disetujui'],
            ['model_type' => 'App\Models\PurchaseOrder', 'description' => 'Purchase order dibuat'],
            ['model_type' => 'App\Models\PurchaseOrder', 'description' => 'Purchase order dikirim ke supplier'],

            // Inventory Activities
            ['model_type' => 'App\Models\GoodReceipt', 'description' => 'Good receipt baru diterima'],
            ['model_type' => 'App\Models\GoodIssue', 'description' => 'Good issue baru dibuat'],
            ['model_type' => 'App\Models\GoodTransfer', 'description' => 'Transfer barang antar gudang'],
            ['model_type' => 'App\Models\ItemMovement', 'description' => 'Perpindahan item dicatat'],

            // Vehicle Activities
            ['model_type' => 'App\Models\Vehicle', 'description' => 'Kendaraan baru ditambahkan'],
            ['model_type' => 'App\Models\Vehicle', 'description' => 'Service kendaraan dilakukan'],
            ['model_type' => 'App\Models\Vehicle', 'description' => 'Pemakaian kendaraan dicatat'],
            ['model_type' => 'App\Models\Vehicle', 'description' => 'Kendaraan dikembalikan'],

            // Inspection Activities
            ['model_type' => 'App\Models\Inspection', 'description' => 'Inspeksi baru dimulai'],
            ['model_type' => 'App\Models\Inspection', 'description' => 'Inspeksi selesai dan disetujui'],
            ['model_type' => 'App\Models\Inspection', 'description' => 'Inspeksi menemukan ketidaksesuaian'],

            // Daily Report Activities
            ['model_type' => 'App\Models\DailyReport', 'description' => 'Laporan harian dibuat'],
            ['model_type' => 'App\Models\DailyReport', 'description' => 'Laporan harian disetujui'],
            ['model_type' => 'App\Models\DailyReport', 'description' => 'Laporan harian direvisi'],

            // Reimbursement Activities
            ['model_type' => 'App\Models\Reimbursement', 'description' => 'Pengajuan reimbursement dibuat'],
            ['model_type' => 'App\Models\Reimbursement', 'description' => 'Reimbursement disetujui'],
            ['model_type' => 'App\Models\Reimbursement', 'description' => 'Reimbursement dibayar'],

            // Notification Activities
            ['model_type' => 'App\Models\AppNotification', 'description' => 'Notifikasi baru dikirim'],
            ['model_type' => 'App\Models\AppNotification', 'description' => 'Notifikasi dibaca oleh user'],

            // System Activities
            ['model_type' => 'App\Models\User', 'description' => 'Backup database berhasil'],
            ['model_type' => 'App\Models\User', 'description' => 'Update sistem dilakukan'],
            ['model_type' => 'App\Models\User', 'description' => 'Maintenance sistem selesai'],
        ];

        // Generate random activities for the past 30 days
        $startDate = now()->subDays(30);
        $endDate = now();

        for ($i = 0; $i < 200; $i++) {
            $randomActivity = $activities[array_rand($activities)];
            $randomUser = $users->random();
            $randomDate = fake()->dateTimeBetween($startDate, $endDate);

            \App\Models\LogActivity::create([
                'users_id' => $randomUser->id,
                'model_type' => $randomActivity['model_type'],
                'model_id' => fake()->numberBetween(1, 100), // Random model ID
                'description' => $randomActivity['description'],
                'created_at' => $randomDate,
                'updated_at' => $randomDate,
            ]);
        }

        // Add some specific recent activities for better dashboard display
        $recentActivities = [
            ['model_type' => 'App\Models\Attendance', 'description' => 'Karyawan melakukan check-in pagi - ' . now()->format('H:i')],
            ['model_type' => 'App\Models\Submission', 'description' => 'Pengajuan cuti tahunan baru diajukan'],
            ['model_type' => 'App\Models\MaterialRequest', 'description' => 'Permintaan material untuk proyek baru'],
            ['model_type' => 'App\Models\Vehicle', 'description' => 'Kendaraan operasional dikembalikan'],
            ['model_type' => 'App\Models\Inspection', 'description' => 'Inspeksi rutin mingguan selesai'],
        ];

        foreach ($recentActivities as $activity) {
            \App\Models\LogActivity::create([
                'users_id' => $users->random()->id,
                'model_type' => $activity['model_type'],
                'model_id' => fake()->numberBetween(1, 50),
                'description' => $activity['description'],
                'created_at' => now()->subMinutes(fake()->numberBetween(1, 60)),
                'updated_at' => now()->subMinutes(fake()->numberBetween(1, 60)),
            ]);
        }
    }
}
