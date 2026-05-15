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
                'name' => 'Gudang Utama',
                'region' => 'Jakarta',
                'email' => 'utama@perusahaan.co.id',
                'contact' => '021-1234567',
                'address' => 'Jl. Industri Raya No. 1, Jakarta Utara',
                'description' => 'Gudang pusat / kantor utama',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'Cabang Bandung',
                'region' => 'Bandung',
                'email' => 'bandung@perusahaan.co.id',
                'contact' => '022-2345678',
                'address' => 'Jl. Soekarno-Hatta No. 50, Bandung',
                'description' => 'Cabang wilayah Bandung',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'Cabang Surabaya',
                'region' => 'Surabaya',
                'email' => 'surabaya@perusahaan.co.id',
                'contact' => '031-3456789',
                'address' => 'Jl. Ahmad Yani No. 30, Surabaya',
                'description' => 'Cabang wilayah Surabaya',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'Cabang Medan',
                'region' => 'Medan',
                'email' => 'medan@perusahaan.co.id',
                'contact' => '061-4567890',
                'address' => 'Jl. Gatot Subroto No. 15, Medan',
                'description' => 'Cabang wilayah Medan',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'Cabang Makassar',
                'region' => 'Makassar',
                'email' => 'makassar@perusahaan.co.id',
                'contact' => '0411-5678901',
                'address' => 'Jl. Urip Sumoharjo No. 20, Makassar',
                'description' => 'Cabang wilayah Makassar',
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ];

        Branch::insert($branches);
    }
}
