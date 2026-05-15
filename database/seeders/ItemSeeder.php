<?php

namespace Database\Seeders;

use App\Models\Item;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ItemSeeder extends Seeder
{
    public function run(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        Item::truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        // Branch 1 = Gudang Utama, Branch 2 = Bandung, Branch 3 = Surabaya
        // Category IDs sesuai ItemCategorySeeder:
        // 1=Bahan Baku, 2=Barang Jadi, 3=Bahan Pembantu, 4=Spare Part, 5=ATK
        // 6=Perlengkapan Gudang, 7=Kemasan, 8=Bahan Kimia, 9=K3, 10=Lain-lain
        $items = [
            // === Gudang Utama (branch 1) ===
            // Bahan Baku
            ['code' => 'BB-001', 'name' => 'Tepung Terigu', 'description' => 'Tepung terigu protein tinggi', 'unit_id' => 1, 'category_id' => 1, 'branch_id' => 1, 'min_stock' => 100],
            ['code' => 'BB-002', 'name' => 'Gula Pasir', 'description' => 'Gula pasir curah', 'unit_id' => 1, 'category_id' => 1, 'branch_id' => 1, 'min_stock' => 50],
            ['code' => 'BB-003', 'name' => 'Minyak Goreng', 'description' => 'Minyak goreng refinery', 'unit_id' => 3, 'category_id' => 1, 'branch_id' => 1, 'min_stock' => 30],
            ['code' => 'BB-004', 'name' => 'Garam', 'description' => 'Garam halus industri', 'unit_id' => 1, 'category_id' => 1, 'branch_id' => 1, 'min_stock' => 20],
            // Barang Jadi
            ['code' => 'BJ-001', 'name' => 'Produk A (500gr)', 'description' => 'Produk jadi kemasan 500 gram', 'unit_id' => 2, 'category_id' => 2, 'branch_id' => 1, 'min_stock' => 200],
            ['code' => 'BJ-002', 'name' => 'Produk B (1kg)', 'description' => 'Produk jadi kemasan 1 kilogram', 'unit_id' => 2, 'category_id' => 2, 'branch_id' => 1, 'min_stock' => 150],
            ['code' => 'BJ-003', 'name' => 'Produk C (Sachet)', 'description' => 'Produk jadi kemasan sachet', 'unit_id' => 2, 'category_id' => 2, 'branch_id' => 1, 'min_stock' => 500],
            // Kemasan
            ['code' => 'KM-001', 'name' => 'Plastik Seal 10kg', 'description' => 'Plastik seal untuk kemasan 10kg', 'unit_id' => 3, 'category_id' => 7, 'branch_id' => 1, 'min_stock' => 50],
            ['code' => 'KM-002', 'name' => 'Karton Box', 'description' => 'Karton box kemasan produk', 'unit_id' => 1, 'category_id' => 7, 'branch_id' => 1, 'min_stock' => 100],
            ['code' => 'KM-003', 'name' => 'Plastik HDPE', 'description' => 'Plastik HDPE curah', 'unit_id' => 1, 'category_id' => 7, 'branch_id' => 1, 'min_stock' => 30],
            // Perlengkapan Gudang
            ['code' => 'PG-001', 'name' => 'Pallet Kayu', 'description' => 'Pallet kayu ukuran standar', 'unit_id' => 1, 'category_id' => 6, 'branch_id' => 1, 'min_stock' => 20],
            ['code' => 'PG-002', 'name' => 'Handpallet', 'description' => 'Handpallet manual kapasitas 2 ton', 'unit_id' => 1, 'category_id' => 6, 'branch_id' => 1, 'min_stock' => 2],
            ['code' => 'PG-003', 'name' => 'Tali Rafia', 'description' => 'Tali rafia rol', 'unit_id' => 6, 'category_id' => 6, 'branch_id' => 1, 'min_stock' => 10],
            // K3
            ['code' => 'K3-001', 'name' => 'Masker Kesehatan', 'description' => 'Masker 3-ply', 'unit_id' => 2, 'category_id' => 9, 'branch_id' => 1, 'min_stock' => 10],
            ['code' => 'K3-002', 'name' => 'Sarung Tangan Latex', 'description' => 'Sarung tangan latex disposable', 'unit_id' => 2, 'category_id' => 9, 'branch_id' => 1, 'min_stock' => 5],
            ['code' => 'K3-003', 'name' => 'Hairnet', 'description' => 'Hairnet produksi', 'unit_id' => 2, 'category_id' => 9, 'branch_id' => 1, 'min_stock' => 10],
            ['code' => 'K3-004', 'name' => 'Sepatu Safety', 'description' => 'Sepatu safety dengan toe cap besi', 'unit_id' => 1, 'category_id' => 9, 'branch_id' => 1, 'min_stock' => 5],
            // ATK
            ['code' => 'ATK-001', 'name' => 'Kertas A4 80gr', 'description' => 'Kertas HVS A4 80 gram', 'unit_id' => 10, 'category_id' => 5, 'branch_id' => 1, 'min_stock' => 5],
            ['code' => 'ATK-002', 'name' => 'Pulpen Ballpoint', 'description' => 'Pulpen ballpoint hitam', 'unit_id' => 2, 'category_id' => 5, 'branch_id' => 1, 'min_stock' => 2],
            ['code' => 'ATK-003', 'name' => 'Stempel & Ink', 'description' => 'Stempel karet + tinta', 'unit_id' => 1, 'category_id' => 5, 'branch_id' => 1, 'min_stock' => 1],
            // Spare Part
            ['code' => 'SP-001', 'name' => 'Oli Mesin SAE-40', 'description' => 'Oli mesin industri SAE-40', 'unit_id' => 11, 'category_id' => 4, 'branch_id' => 1, 'min_stock' => 5],
            ['code' => 'SP-002', 'name' => 'Filter Udara', 'description' => 'Filter udara kompresor', 'unit_id' => 1, 'category_id' => 4, 'branch_id' => 1, 'min_stock' => 3],
            ['code' => 'SP-003', 'name' => 'V-Belt', 'description' => 'V-Belt mesin produksi', 'unit_id' => 1, 'category_id' => 4, 'branch_id' => 1, 'min_stock' => 5],

            // === Cabang Bandung (branch 2) ===
            ['code' => 'BDG-BB-001', 'name' => 'Tepung Terigu', 'description' => 'Tepung terigu protein sedang', 'unit_id' => 1, 'category_id' => 1, 'branch_id' => 2, 'min_stock' => 50],
            ['code' => 'BDG-BJ-001', 'name' => 'Produk A (500gr)', 'description' => 'Produk jadi kemasan 500 gram', 'unit_id' => 2, 'category_id' => 2, 'branch_id' => 2, 'min_stock' => 100],
            ['code' => 'BDG-KM-001', 'name' => 'Plastik Seal 10kg', 'description' => 'Plastik seal kemasan', 'unit_id' => 3, 'category_id' => 7, 'branch_id' => 2, 'min_stock' => 20],
            ['code' => 'BDG-K3-001', 'name' => 'Masker Kesehatan', 'description' => 'Masker 3-ply disposable', 'unit_id' => 2, 'category_id' => 9, 'branch_id' => 2, 'min_stock' => 5],

            // === Cabang Surabaya (branch 3) ===
            ['code' => 'SBY-BB-001', 'name' => 'Tepung Terigu', 'description' => 'Tepung terigu protein tinggi', 'unit_id' => 1, 'category_id' => 1, 'branch_id' => 3, 'min_stock' => 60],
            ['code' => 'SBY-BJ-001', 'name' => 'Produk B (1kg)', 'description' => 'Produk jadi kemasan 1 kilogram', 'unit_id' => 2, 'category_id' => 2, 'branch_id' => 3, 'min_stock' => 80],
            ['code' => 'SBY-K3-001', 'name' => 'Sarung Tangan Latex', 'description' => 'Sarung tangan latex disposable', 'unit_id' => 2, 'category_id' => 9, 'branch_id' => 3, 'min_stock' => 5],
        ];

        $now = now();
        foreach ($items as &$item) {
            $item['created_at'] = $now;
            $item['updated_at'] = $now;
        }

        Item::insert($items);

        $this->command->info('Items seeded: ' . count($items) . ' items across 3 branches.');
    }
}
