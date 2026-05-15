<?php

namespace Database\Seeders;

use App\Models\ItemCategory;
use Illuminate\Database\Seeder;

class ItemCategorySeeder extends Seeder
{
    public function run(): void
    {
        $rows = [
            ['name' => 'Bahan Baku', 'description' => 'Bahan mentah untuk produksi'],
            ['name' => 'Barang Jadi', 'description' => 'Produk yang sudah selesai diproduksi'],
            ['name' => 'Bahan Pembantu', 'description' => 'Bahan pendukung proses produksi'],
            ['name' => 'Spare Part & Peralatan', 'description' => 'Suku cadang mesin dan peralatan'],
            ['name' => 'Alat Tulis Kantor (ATK)', 'description' => 'Perlengkapan administrasi kantor'],
            ['name' => 'Perlengkapan Gudang', 'description' => 'Peralatan operasional gudang'],
            ['name' => 'Kemasan & Packaging', 'description' => 'Material kemasan produk'],
            ['name' => 'Bahan Kimia', 'description' => 'Bahan kimia industri'],
            ['name' => 'Peralatan Keselamatan (K3)', 'description' => 'Alat pelindung diri dan keselamatan kerja'],
            ['name' => 'Lain-lain', 'description' => null],
        ];

        foreach ($rows as $row) {
            ItemCategory::firstOrCreate(['name' => $row['name']], $row);
        }
    }
}
