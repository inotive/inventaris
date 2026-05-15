<?php

namespace Database\Seeders;

use App\Models\VehicleType;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class VehicleTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $rows = [
            ['name' => 'Hino Tipe 300', 'category' => 'Truk Logistik'],
            ['name' => 'Daihatsu Grandmax', 'category' => 'Pick-Up'],
            ['name' => 'Isuzu Traga', 'category' => 'Pick-Up Ringan'],
            ['name' => 'Mitsubishi L300', 'category' => 'Kendaraan Niaga'],
            ['name' => 'Hino Dutro 130 HD', 'category' => 'Truck Box'],
        ];

        foreach ($rows as $row) {
            VehicleType::firstOrCreate(['name' => $row['name']], $row);
        }
    }
}
