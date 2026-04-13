<?php

namespace Database\Seeders;

use App\Models\Position;
use Illuminate\Database\Seeder;

class PositionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $positions = [
            'Direktur Utama',
            'Manajer Operasional',
            'Manajer Gudang',
            'Manajer Logistik',
            'Manajer Keuangan',
            'Supervisor Gudang',
            'Supervisor Pengiriman',
            'Kepala Gudang',
            'Staff Gudang',
            'Staff Logistik',
            'Staff Administrasi',
            'Staff Keuangan',
            'Staff IT',
            'Staff HRD',
            'Operator Gudang',
            'Packer',
            'Driver',
            'Keamanan',
            'Admin',
            'Accounting',
        ];

        foreach ($positions as $position) {
            Position::firstOrCreate(['name' => $position]);
        }
    }
}
