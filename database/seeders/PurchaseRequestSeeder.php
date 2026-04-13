<?php

namespace Database\Seeders;

use App\Models\Item;
use App\Models\Employee;
use App\Models\Department;
use App\Models\PurchaseRequest;
use App\Models\PurchaseRequestItem;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class PurchaseRequestSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $employees = Employee::query()->select('id')->pluck('id')->all();
        $departments = Department::query()->select('id')->pluck('id')->all();
        $items = Item::query()->select('id')->pluck('id')->all();

        if (empty($employees) || empty($departments) || empty($items)) {
            $this->command?->warn('PurchaseRequestSeeder skipped: employees/departments/items not found.');
            return;
        }

        // Create 20 sample Purchase Requests in the current and previous month
        for ($i = 0; $i < 20; $i++) {
            $date = now()->subDays(rand(0, 40));

            $mr = new PurchaseRequest();
            $mr->department_id = $departments[array_rand($departments)];
            $mr->requested_by = $employees[array_rand($employees)];
            $mr->requested_at = $date; // assign Carbon instance for proper date cast
            $mr->status = collect(['on_progress', 'approved', 'rejected', 'canceled', 'partial_approved'])->random();
            $mr->requirement = fake()->boolean(40) ? fake()->sentence() : null;
            // request_no auto-generated in model booted()
            $mr->save();

            // Attach 1-3 items
            $countItems = rand(1, 3);
            $picked = collect($items)->shuffle()->take($countItems);
            foreach ($picked as $itemId) {
                PurchaseRequestItem::create([
                    'purchase_request_id' => $mr->id,
                    'item_id' => $itemId,
                    'quantity_requested' => rand(1, 10),
                    'quantity_approved' => in_array($mr->status, ['approved', 'partial_approved']) ? rand(0, 10) : 0,
                    'status' => match ($mr->status) {
                        'approved', 'partial_approved' => 'approved',
                        'rejected' => 'rejected',
                        'canceled' => 'canceled',
                        default => 'on_progress',
                    },
                    'note' => fake()->boolean(20) ? fake()->sentence() : null,
                ]);
            }
        }

        $this->command?->info('PurchaseRequestItem: created sample Purchase Requests with items.');
    }
}
