<?php

namespace Database\Seeders;

use App\Models\Item;
use App\Models\Branch;
use App\Models\Employee;
use App\Models\GoodTransfer;
use Illuminate\Database\Seeder;
use App\Models\GoodTransferItem;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class GoodTransferSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $branchIds    = Branch::pluck('id')->toArray();
        $employeeIds = Employee::pluck('id')->toArray();
        $itemIds    = Item::pluck('id')->toArray();

        if (empty($branchIds) || empty($employeeIds)) {
            $this->command->warn('Seeder Good Transfer dilewati: branch/employee request kosong.');
            return;
        }

        for ($i = 0; $i < 10; $i++) {
            $fromBranch = $branchIds[array_rand($branchIds)];
            do {
                $toBranch = $branchIds[array_rand($branchIds)];
            } while ($toBranch === $fromBranch);

            $sender = $employeeIds[array_rand($employeeIds)];
            do {
                $receiver = $employeeIds[array_rand($employeeIds)];
            } while ($receiver === $sender);

            $goodTransfer = GoodTransfer::create([
                'from_branch'    => $fromBranch,
                'to_branch'      => $toBranch,
                'sent_by'        => $sender,
                'received_by'    => $receiver,
                'purpose'        => fake()->boolean(50) ? fake()->sentence() : null,
                'status'         => fake()->randomElement(['Shipped', 'Received', 'Canceled']),
                'transferred_at' => now()->subDays(rand(0, 30)),
            ]);

            for ($j = 0; $j < rand(2, 5); $j++) {
                GoodTransferItem::create([
                    'good_transfer_id'     => $goodTransfer->id,
                    'item_id'              => $itemIds[array_rand($itemIds)],
                    'quantity_transferred' => rand(1, 20),
                    'note'                 => 'test',
                ]);
            }
        }
    }
}
