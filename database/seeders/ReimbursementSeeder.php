<?php

namespace Database\Seeders;

use App\Models\Reimbursement;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ReimbursementSeeder extends Seeder
{
    public function run(): void
    {
        // Seed sample reimbursements for employee_id 1 and 2
        $employees = [1, 2];
        foreach ($employees as $empId) {
            for ($i = 1; $i <= 3; $i++) {
                Reimbursement::create([
                    'employee_id' => $empId,
                    'title' => 'Reimbursement #' . $i,
                    'description' => 'Reimbursement sample data for employee ' . $empId,
                    'event_date' => now()->subDays(rand(1, 20))->format('Y-m-d'),
                    'amount' => rand(50, 300) * 1000,
                    'currency' => 'IDR',
                    'attachment_path' => null,
                    'status' => 'pending', // pending | approved | rejected
                    'approved_by' => null,
                    'approved_at' => null,
                ]);
            }
        }
    }
}
