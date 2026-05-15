<?php

namespace Database\Seeders;

use App\Models\Item;
use App\Models\ItemMovement;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class ItemMovementSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // If there are no items, nothing to seed
        $items = Item::query()->take(20)->get();
        if ($items->isEmpty()) {
            $this->command?->warn('No items found. Skipping ItemMovementSeeder.');
            return;
        }

        DB::transaction(function () use ($items) {
            foreach ($items as $item) {
                // Start with the current item stock or 0 if null
                $lastStock = (float) ($item->stock ?? 0);

                // Generate 8 movements in the last 60 days
                $date = Carbon::now()->subDays(60);
                for ($i = 0; $i < 8; $i++) {
                    $date = $date->addDays(random_int(3, 10));

                    // Randomly pick a type
                    $types = ['in', 'out', 'adjust_in', 'adjust_out', 'transfer_in', 'transfer_out', 'return_in'];
                    $type = $types[array_rand($types)];

                    // Random warehouse IDs (1..5) — no FK required
                    $warehouseId = random_int(1, 5);

                    // Quantity
                    $qty = random_int(1, 50);
                    $delta = in_array($type, ['in', 'adjust_in', 'transfer_in', 'return_in'], true)
                        ? $qty
                        : -$qty;

                    $currentStock = max(0, $lastStock + $delta);

                    ItemMovement::create([
                        'item_id' => $item->id,
                        'branch_id' => $warehouseId,
                        'type' => $type,
                        'quantity' => $qty,
                        'reference_type' => match ($type) {
                            'in' => 'purchase_order',
                            'transfer_in', 'transfer_out' => 'transfer_stock',
                            'return_in' => 'sales_return',
                            'adjust_in', 'adjust_out' => 'adjustment',
                            default => 'manual',
                        },
                        'reference_id' => null,
                        'last_stock' => $lastStock,
                        'current_stock' => $currentStock,
                        'notes' => null,
                        'created_at' => $date->copy(),
                        'updated_at' => $date->copy(),
                    ]);

                    $lastStock = $currentStock;
                }

                // Optionally sync final stock to items table
                $item->update(['stock' => $lastStock]);
            }
        });
    }
}
