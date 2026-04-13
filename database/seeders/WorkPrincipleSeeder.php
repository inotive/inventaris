<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\WorkPrinciple;

class WorkPrincipleSeeder extends Seeder
{
    public function run(): void
    {
        $data = [
            [ 'category' => 'prinsip', 'title' => 'Integritas', 'description' => 'Selalu jujur dan bertanggung jawab.', 'created_by' => 1, 'img_url' => null ],
            [ 'category' => 'prinsip', 'title' => 'Kerjasama', 'description' => 'Saling mendukung untuk mencapai tujuan.', 'created_by' => 1, 'img_url' => null ],
            [ 'category' => 'etos kerja', 'title' => 'Disiplin', 'description' => 'Tepat waktu dan konsisten.', 'created_by' => 1, 'img_url' => null ],
            [ 'category' => 'etos kerja', 'title' => 'Profesional', 'description' => 'Bekerja sesuai standar terbaik.', 'created_by' => 1, 'img_url' => null ],
        ];
        foreach ($data as $row) { WorkPrinciple::create($row); }
    }
}
