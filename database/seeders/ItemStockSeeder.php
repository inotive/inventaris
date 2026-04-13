<?php

namespace Database\Seeders;

use App\Models\Item;
use App\Models\GoodIssue;
use App\Models\PurchaseOrder;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ItemStockSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $items = Item::pluck('id')->toArray();
        $purchaseOrders = PurchaseOrder::pluck('id')->toArray();
        $goodIssues = GoodIssue::pluck('id')->toArray();

        if (empty($items)) {
            $this->command?->warn('ItemStockSeeder skipped: no items found.');
            return;
        }

        DB::table('item_stocks')->truncate();

        // Buat history stok untuk setiap item
        foreach ($items as $itemId) {
            $currentStock = 0;
            $baseDate = now()->subDays(60);

            // Buat 5-10 stok masuk (In) dulu
            $inCount = rand(5, 10);
            for ($i = 0; $i < $inCount; $i++) {
                $amount = rand(10, 100);
                $initial = $currentStock;
                $currentStock += $amount;
                $date = $baseDate->copy()->addDays(rand(0, 30));

                $sourceType = !empty($purchaseOrders) ? get_class(PurchaseOrder::first()) : null;
                $sourceId = !empty($purchaseOrders) ? $purchaseOrders[array_rand($purchaseOrders)] : null;

                DB::table('item_stocks')->insert([
                    'item_id'       => $itemId,
                    'type'          => 'In',
                    'source_type'   => $sourceType,
                    'source_id'     => $sourceId,
                    'amount'        => $amount,
                    'initial_stock' => $initial,
                    'last_stock'    => $currentStock,
                    'tanggal'       => $date->toDateString(),
                    'note'          => 'Stok awal / pembelian',
                    'created_at'    => $date,
                    'updated_at'    => $date,
                ]);
            }

            // Buat 3-6 stok keluar (Out) setelahnya
            if ($currentStock > 0) {
                $outCount = rand(3, 6);
                for ($i = 0; $i < $outCount; $i++) {
                    $maxOut = max(1, intval($currentStock * 0.3));
                    $amount = rand(1, $maxOut);
                    if ($amount > $currentStock) break;

                    $initial = $currentStock;
                    $currentStock -= $amount;
                    $date = now()->subDays(rand(0, 20));

                    $sourceType = !empty($goodIssues) ? get_class(GoodIssue::first()) : null;
                    $sourceId = !empty($goodIssues) ? $goodIssues[array_rand($goodIssues)] : null;

                    DB::table('item_stocks')->insert([
                        'item_id'       => $itemId,
                        'type'          => 'Out',
                        'source_type'   => $sourceType,
                        'source_id'     => $sourceId,
                        'amount'        => $amount,
                        'initial_stock' => $initial,
                        'last_stock'    => $currentStock,
                        'tanggal'       => $date->toDateString(),
                        'note'          => 'Pemakaian barang',
                        'created_at'    => $date,
                        'updated_at'    => $date,
                    ]);
                }
            }
        }

        $count = DB::table('item_stocks')->count();
        $this->command?->info("ItemStockSeeder: created {$count} stock movement records.");
    }
}
