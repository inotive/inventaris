<?php

namespace Database\Seeders;

use App\Models\Branch;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class BranchSeeder extends Seeder
{
    public function run(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        Branch::truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $now = now();

        $branches = [
            [
                'name' => 'Gudang Cabang Sekerat',
                'region' => 'Kutai Timur',
                'email' => '-',
                'contact' => '-',
                'address' => 'Sekerat',
                'description' => 'Gudang Cabang Sekerat',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'Gudang Cabang Teluk Pandan',
                'region' => 'Kutai Timur',
                'email' => '-',
                'contact' => '-',
                'address' => 'Teluk Pandan',
                'description' => 'Gudang Cabang Teluk Pandan',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'Gudang Cabang Telen',
                'region' => 'Kutai Timur',
                'email' => '-',
                'contact' => '-',
                'address' => 'Telen',
                'description' => 'Gudang Cabang Telen',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'Gudang Cabang Kaubun',
                'region' => 'Kutai Timur',
                'email' => '-',
                'contact' => '-',
                'address' => 'Kaubun',
                'description' => 'Gudang Cabang Kaubun',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'Gudang Cabang Rantau Pulung',
                'region' => 'Kutai Timur',
                'email' => '-',
                'contact' => '-',
                'address' => 'Rantau Pulung',
                'description' => 'Gudang Cabang Rantau Pulung',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'Gudang Cabang Muara Wahau',
                'region' => 'Kutai Timur',
                'email' => '-',
                'contact' => '-',
                'address' => 'Muara Wahau',
                'description' => 'Gudang Cabang Muara Wahau',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'Gudang Cabang Muara Ancalong',
                'region' => 'Kutai Timur',
                'email' => '-',
                'contact' => '-',
                'address' => 'Muara Ancalong',
                'description' => 'Gudang Cabang Muara Ancalong',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'Gudang Cabang Kaliorang',
                'region' => 'Kutai Timur',
                'email' => '-',
                'contact' => '-',
                'address' => 'Kaliorang',
                'description' => 'Gudang Cabang Kaliorang',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'Gudang Cabang Busang',
                'region' => 'Kutai Timur',
                'email' => '-',
                'contact' => '-',
                'address' => 'Busang',
                'description' => 'Gudang Cabang Busang',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'Gudang Cabang Bengalon',
                'region' => 'Kutai Timur',
                'email' => '-',
                'contact' => '-',
                'address' => 'Bengalon',
                'description' => 'Gudang Cabang Bengalon',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'Gudang Utama Cabang Sangatta',
                'region' => 'Kutai Timur',
                'email' => 'sinergiputraborneo@gmail.com',
                'contact' => '085353528662',
                'address' => 'Jalan Karya Etam No. 164 RT 12 Sangatta Utara, Kec. Sangatta Utara Kutai Timur',
                'description' => 'Gudang Utama Cabang Sangatta',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'Gudang Pusat',
                'region' => 'Balikpapan',
                'email' => 'sinergiputraborneo@gmail.com',
                'contact' => '082195666151',
                'address' => 'Grandcity Cluster Norbhville Blok i2 No. 10',
                'description' => 'Gudang Pusat',
                'created_at' => $now,
                'updated_at' => $now,
            ]
        ];

        Branch::insert($branches);
    }
}
