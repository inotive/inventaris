<?php

namespace Database\Seeders;

use App\Models\Employee;
use App\Models\Item;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\PurchaseRequest;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class PurchaseOrderSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $requestIds    = PurchaseRequest::pluck('id')->toArray();
        $employeeIds = Employee::pluck('id')->toArray();
        $itemIds    = Item::pluck('id')->toArray();

        if (empty($employeeIds) || empty($requestIds)) {
            $this->command->warn('Seeder Purchase Order dilewati: empoloyee/purchase request kosong.');
            return;
        }

        // Buat 10 Purchase Order
        for ($i = 0; $i < 40; $i++) {
            $purchaseOrder = PurchaseOrder::create([
                'request_id'  => $requestIds[array_rand($requestIds)],
                'ordered_by'  => $employeeIds[array_rand($employeeIds)],
                'ordered_at'  => now()->subDays(rand(0, 30)),
                'vendor'      => fake()->company(),
                'note'        => fake()->boolean(40) ? fake()->sentence() : null,
            ]);

            // Tambah 2–5 item dummy untuk setiap Purchase Order
            for ($j = 0; $j < rand(2, 5); $j++) {
                PurchaseOrderItem::create([
                    'purchase_order_id'     => $purchaseOrder->id,
                    'item_id'               => $itemIds[array_rand($itemIds)],
                    'quantity_ordered'    => rand(1, 20),
                    'note'                  => 'test',
                    'cost' => mt_rand(12000, 2400000),
                ]);
            }
        }
    }
}
