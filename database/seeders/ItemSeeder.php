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

        $branches = \App\Models\Branch::all();
        $now = now();

        $itemTemplates = [
            // Bahan Baku
            ['code_prefix' => 'BB', 'name' => 'Tepung Terigu', 'description' => 'Tepung terigu protein tinggi', 'unit_id' => 1, 'category_id' => 1, 'min_stock' => 100],
            ['code_prefix' => 'BB', 'name' => 'Gula Pasir', 'description' => 'Gula pasir curah', 'unit_id' => 1, 'category_id' => 1, 'min_stock' => 50],
            ['code_prefix' => 'BB', 'name' => 'Minyak Goreng', 'description' => 'Minyak goreng refinery', 'unit_id' => 1, 'category_id' => 1, 'min_stock' => 30],
            // Barang Jadi
            ['code_prefix' => 'BJ', 'name' => 'Produk A (500gr)', 'description' => 'Produk jadi kemasan 500 gram', 'unit_id' => 1, 'category_id' => 2, 'min_stock' => 200],
            ['code_prefix' => 'BJ', 'name' => 'Produk B (1kg)', 'description' => 'Produk jadi kemasan 1 kilogram', 'unit_id' => 1, 'category_id' => 2, 'min_stock' => 150],
            // Perlengkapan Gudang
            ['code_prefix' => 'PG', 'name' => 'Pallet Kayu', 'description' => 'Pallet kayu ukuran standar', 'unit_id' => 1, 'category_id' => 6, 'min_stock' => 20],
            ['code_prefix' => 'PG', 'name' => 'Handpallet', 'description' => 'Handpallet manual kapasitas 2 ton', 'unit_id' => 1, 'category_id' => 6, 'min_stock' => 2],
            // K3
            ['code_prefix' => 'K3', 'name' => 'Masker Kesehatan', 'description' => 'Masker 3-ply', 'unit_id' => 1, 'category_id' => 9, 'min_stock' => 10],
            ['code_prefix' => 'K3', 'name' => 'Sepatu Safety', 'description' => 'Sepatu safety dengan toe cap besi', 'unit_id' => 1, 'category_id' => 9, 'min_stock' => 5],
        ];

        $itemsToInsert = [];
        $itemCount = 0;

        foreach ($branches as $branch) {
            foreach ($itemTemplates as $index => $template) {
                $itemCount++;
                $itemsToInsert[] = [
                    'code' => $template['code_prefix'] . '-' . str_pad($index + 1, 3, '0', STR_PAD_LEFT),
                    'name' => $template['name'],
                    'description' => $template['description'],
                    'unit_id' => $template['unit_id'],
                    'category_id' => $template['category_id'],
                    'branch_id' => $branch->id,
                    'min_stock' => $template['min_stock'],
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }
        }

        Item::insert($itemsToInsert);

        $this->command->info('Items seeded: ' . count($itemsToInsert) . ' items across ' . $branches->count() . ' branches.');
    }
}
