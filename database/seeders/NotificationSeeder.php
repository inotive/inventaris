<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Date;

class NotificationSeeder extends Seeder
{
    public function run(): void
    {
        // Target user with username 'superadmin'
        $userId = DB::table('users')->where('username', 'superadmin')->value('id');
        if (!$userId) return; // quietly skip if not present

        DB::table('notifications')->insert([
            [
                'user_id' => 1,
                'title' => 'Pengajuan Cuti',
                'category' => 'Pengajuan',
                'model_type' => null,
                'model_id' => null,
                'pesan' => 'Pengajuan Cuti Sudah disetujui',
                'status' => 0,
                'is_success' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'user_id' => 1,
                'title' => 'Pengajuan Lembur',
                'category' => 'pengajuan',
                'model_type' => 'App\\Models\\EmployeeOvertime',
                'model_id' => 1,
                'pesan' => 'Pengajuan anda sudah disetujui',
                'status' => 0,
                'is_success' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'user_id' => 1,
                'title' => 'Absensi Masuk',
                'category' => 'Absensi',
                'model_type' => 'App\\Models\\Attendance',
                'model_id' => 1,
                'pesan' => 'Absensi Masuk Berhasil pada pukul 08.00',
                'status' => 1,
                'is_success' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
