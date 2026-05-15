<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class ShiftSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Set starting ID to 6
        DB::statement('ALTER TABLE shifts AUTO_INCREMENT = 6');

        DB::table('shifts')->insert([
            // Office & Distribusi
            [
                'name' => 'Shift Pagi Office',
                'code' => 'S1',
                'start_time' => '08:00:00',
                'late_tolerance' => '08:15:00',
                'end_time' => '17:00:00',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'One Shift Distribusi',
                'code' => 'S2',
                'start_time' => '07:00:00',
                'late_tolerance' => '07:15:00',
                'end_time' => '19:00:00',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            // NOTE: 'One Shift Distribusi new' will be appended at the end to match desired ID order

            // Produksi BPP
            [
                'name' => 'Shift 1 Produksi BPP',
                'code' => 'S4',
                'start_time' => '06:00:00',
                'late_tolerance' => '06:15:00',
                'end_time' => '14:00:00',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            // Order BPP: 1, 3, 2 (to match desired listing)
            [
                'name' => 'Shift 3 Produksi BPP',
                'code' => 'S6',
                'start_time' => '22:00:00',
                'late_tolerance' => '22:15:00',
                'end_time' => '06:00:00',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Shift 2 Produksi BPP',
                'code' => 'S5',
                'start_time' => '14:00:00',
                'late_tolerance' => '14:15:00',
                'end_time' => '22:00:00',
                'created_at' => now(),
                'updated_at' => now(),
            ],

            // Office Siang
            [
                'name' => 'Shift Siang Office',
                'code' => 'S7',
                'start_time' => '14:00:00',
                'late_tolerance' => '14:15:00',
                'end_time' => '23:00:00',
                'created_at' => now(),
                'updated_at' => now(),
            ],

            // Produksi SMD
            [
                'name' => 'Shift 1 Produksi SMD',
                'code' => 'S8',
                'start_time' => '06:00:00',
                'late_tolerance' => '06:15:00',
                'end_time' => '14:00:00',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Shift 2 Produksi SMD',
                'code' => 'S9',
                'start_time' => '14:00:00',
                'late_tolerance' => '14:15:00',
                'end_time' => '22:00:00',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Shift 3 Produksi SMD',
                'code' => 'S10',
                'start_time' => '22:00:00',
                'late_tolerance' => '22:15:00',
                'end_time' => '06:00:00',
                'created_at' => now(),
                'updated_at' => now(),
            ],

            // Muat SMD
            [
                'name' => 'Shift 1 Muat SMD',
                'code' => 'S11',
                'start_time' => '08:00:00',
                'late_tolerance' => '08:15:00',
                'end_time' => '16:00:00',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Shift 2 Muat SMD',
                'code' => 'S12',
                'start_time' => '18:00:00',
                'late_tolerance' => '18:15:00',
                'end_time' => '02:00:00',
                'created_at' => now(),
                'updated_at' => now(),
            ],

            // CS SMD
            [
                'name' => 'Shift 1 CS SMD',
                'code' => 'S13',
                'start_time' => '06:00:00',
                'late_tolerance' => '06:15:00',
                'end_time' => '14:00:00',
                'created_at' => now(),
                'updated_at' => now(),
            ],

            // Sealer
            [
                'name' => 'Shift 1 Sealer',
                'code' => 'S14',
                'start_time' => '08:00:00',
                'late_tolerance' => '08:15:00',
                'end_time' => '16:00:00',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Shift 2 Sealer',
                'code' => 'S15',
                'start_time' => '19:00:00',
                'late_tolerance' => '19:15:00',
                'end_time' => '00:00:00',
                'created_at' => now(),
                'updated_at' => now(),
            ],

            // Muat BPP
            [
                'name' => 'SHIFT 1 MUAT BPP',
                'code' => 'S16',
                'start_time' => '00:00:00',
                'late_tolerance' => '00:15:00',
                'end_time' => '08:00:00',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'SHIFT 2 MUAT BPP',
                'code' => 'S17',
                'start_time' => '13:00:00',
                'late_tolerance' => '13:15:00',
                'end_time' => '21:00:00',
                'created_at' => now(),
                'updated_at' => now(),
            ],

            // CS BPP
            [
                'name' => 'SHIFT 1 CS BPP',
                'code' => 'S18',
                'start_time' => '08:00:00',
                'late_tolerance' => '08:15:00',
                'end_time' => '16:00:00',
                'created_at' => now(),
                'updated_at' => now(),
            ],

            // Distribusi BPP
            [
                'name' => 'SHIFT 1 DISTRIBUSI BPP',
                'code' => 'S19',
                'start_time' => '07:00:00',
                'late_tolerance' => '07:15:00',
                'end_time' => '19:00:00',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            // Append 'One Shift Distribusi new' as the last entry to get highest ID
            [
                'name' => 'One Shift Distribusi new',
                'code' => 'S3',
                'start_time' => '08:00:00',
                'late_tolerance' => '08:15:00',
                'end_time' => '17:00:00',
                'created_at' => now(),
                'updated_at' => now(),
            ],

        ]);
    }
}
