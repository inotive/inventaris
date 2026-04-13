<?php

namespace Database\Seeders;

use App\Models\VehicleService;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class VehicleServiceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $rows = [
            ['vehicle_id' => 1, 'cost' => 450000,  'date' => '2026-02-28' , 'distance' => 10000, 'note' => 'Lorem ipsum dolor sit amet consectetur adipisicing elit.   ex dicta.'],
            ['vehicle_id' => 2, 'cost' => 240000,  'date' => '2026-02-28' , 'distance' => 25600, 'note' => 'Lorem ipsum dolor sit amet consectetur adipisicing elit.'],
            ['vehicle_id' => 2, 'cost' => 680000,  'date' => '2026-03-29' , 'distance' => 12000, 'note' => 'Nostrum, mollitia velit. Rerum eligendi enim'],
            ['vehicle_id' => 3, 'cost' => 750000,  'date' => '2026-02-28' , 'distance' => 8500, 'note' => 'accusamus provident odit'],
            ['vehicle_id' => 3, 'cost' => 750000,  'date' => '2026-03-27' , 'distance' => 124000, 'note' => 'repellendus fugit hic impedit, iste modi,'],
        ];

        foreach ($rows as $row) {
            VehicleService::firstOrCreate(['vehicle_id' => $row['vehicle_id']], $row);
        }
    }
}
