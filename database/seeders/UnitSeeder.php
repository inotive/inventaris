<?php

namespace Database\Seeders;

use App\Models\Unit;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class UnitSeeder extends Seeder
{
    public function run(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        Unit::truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $now = now();

        $units = [
            // Kemasan / Packaging
            ['name' => 'PCS', 'short_name' => 'Pcs'],
            ['name' => 'BOX', 'short_name' => 'Box'],
            ['name' => 'BALL', 'short_name' => 'Ball'],
            ['name' => 'DUS', 'short_name' => 'Dus'],
            ['name' => 'KARTON', 'short_name' => 'Ktn'],
            ['name' => 'SACK', 'short_name' => 'Sck'],
            ['name' => 'ROLL', 'short_name' => 'Roll'],
            ['name' => 'LEMBAR', 'short_name' => 'Lbr'],
            ['name' => 'BOTOL', 'short_name' => 'Btl'],
            ['name' => 'KALENG', 'short_name' => 'Klg'],
            ['name' => 'JERIGEN', 'short_name' => 'Jrg'],
            ['name' => 'PAK', 'short_name' => 'Pak'],
            ['name' => 'SET', 'short_name' => 'Set'],
            ['name' => 'PAKET', 'short_name' => 'Pkt'],
            ['name' => 'UNIT', 'short_name' => 'Unit'],

            // Berat
            ['name' => 'KILOGRAM', 'short_name' => 'Kg'],
            ['name' => 'GRAM', 'short_name' => 'g'],
            ['name' => 'TON', 'short_name' => 'Ton'],

            // Volume
            ['name' => 'LITER', 'short_name' => 'L'],
            ['name' => 'MILLILITER', 'short_name' => 'mL'],

            // Panjang
            ['name' => 'METER', 'short_name' => 'm'],
            ['name' => 'CENTIMETER', 'short_name' => 'cm'],

            // Lain-lain
            ['name' => 'RIM', 'short_name' => 'Rim'],
            ['name' => 'LUSIN', 'short_name' => 'Lsn'],
            ['name' => 'KODI', 'short_name' => 'Kodi'],
            ['name' => 'GROSS', 'short_name' => 'Grs'],
        ];

        // Add timestamps
        foreach ($units as &$unit) {
            $unit['created_at'] = $now;
            $unit['updated_at'] = $now;
        }

        Unit::insert($units);
    }
}
