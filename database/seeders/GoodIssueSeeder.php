<?php

namespace Database\Seeders;

use App\Models\Item;
use App\Models\GoodIssue;
use App\Models\Department;
use App\Models\GoodIssueItem;
use Illuminate\Support\Str;
use App\Models\MaterialRequest;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class GoodIssueSeeder extends Seeder
{
    public function run()
    {
        $departmentIds = Department::pluck('id')->toArray();
        $requestIds    = MaterialRequest::pluck('id')->toArray();
        $itemIds    = Item::pluck('id')->toArray();

        if (empty($departmentIds) || empty($requestIds)) {
            $this->command->warn('Seeder Good Issue dilewati: department/material request kosong.');
            return;
        }

        // Buat 10 Good Issue
        for ($i = 0; $i < 10; $i++) {
            $goodIssue = GoodIssue::create([
                'request_id'    => $requestIds[array_rand($requestIds)],
                'department_id' => $departmentIds[array_rand($departmentIds)],
                'date'          => now()->subDays(rand(0, 30)),
                'requirement'   => fake()->boolean(50) ? fake()->sentence() : null,
                'status'        => fake()->randomElement(['draft', 'issued', 'approved']),
            ]);

            // Tambah 2–5 item dummy untuk setiap Good Issue
            for ($j = 0; $j < rand(2, 5); $j++) {
                GoodIssueItem::create([
                    'good_issue_id'     => $goodIssue->id,
                    'item_id'           => $itemIds[array_rand($itemIds)],
                    'quantity_issued'   => rand(1, 20),
                    'note'              => 'test',
                ]);
            }
        }
    }
}
