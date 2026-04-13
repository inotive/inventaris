<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Report;

class ReportSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Report::create([
            'title' => 'Laporan Keuangan Bulan Januari',
            'description' => 'Laporan detail transaksi dan neraca keuangan bulan Januari.',
            'image' => 'reports/sample1.jpg',
        ]);

        Report::create([
            'title' => 'Laporan Proyek Infrastruktur',
            'description' => 'Dokumentasi progress pembangunan jalan raya tahap pertama.',
            'image' => 'reports/sample2.jpg',
        ]);

        Report::create([
            'title' => 'Laporan Audit Internal',
            'description' => 'Audit internal untuk memastikan kepatuhan terhadap SOP.',
            'image' => 'reports/sample3.jpg',
        ]);
    }
}
