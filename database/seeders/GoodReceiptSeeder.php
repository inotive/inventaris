<?php

namespace Database\Seeders;

use App\Models\Item;
use App\Models\Employee;
use App\Models\GoodReceipt;
use App\Models\GoodTransfer;
use App\Models\PurchaseOrder;
use App\Models\GoodReceiptItem;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class GoodReceiptSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $employeeIds    = Employee::pluck('id')->toArray();
        $orderIds       = PurchaseOrder::pluck('id')->toArray();
        $transferIds    = GoodTransfer::pluck('id')->toArray();
        $itemIds        = Item::pluck('id')->toArray();

        if (empty($employeeIds)) {
            $this->command->warn('Seeder Good Receipt dilewati: employee kosong.');
            return;
        }

        // Buat 20 Good Issue
        for ($i = 0; $i < 20; $i++) {
            $source = fake()->randomElement(['Pembelian', 'Pemindahan']);

            $goodReceipt = GoodReceipt::create([
                'employee_id' => $employeeIds[array_rand($employeeIds)],
                'order_id'    => $source === 'Pembelian' ? $orderIds[array_rand($orderIds)] : null,
                'transfer_id' => $source === 'Pemindahan' ? $transferIds[array_rand($transferIds)] : null,
                'source'      => $source,
                'note'        => fake()->boolean(50) ? fake()->sentence() : null,
                'received_at' => now()->subDays(rand(0, 30)),
            ]);

            // Tambah 2–5 item dummy untuk setiap Good Receipt
            for ($j = 0; $j < rand(2, 5); $j++) {
                GoodReceiptItem::create([
                    'good_receipt_id'   => $goodReceipt->id,
                    'item_id'           => $itemIds[array_rand($itemIds)],
                    'quantity_received' => rand(1, 20),
                    'note'              => 'test',
                ]);
            }
        }
    }
}
