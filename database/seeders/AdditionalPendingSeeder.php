<?php

namespace Database\Seeders;

use App\Enums\EmployeeOvertimeStatusEnum;
use App\Models\Employee;
use App\Models\EmployeeOvertime;
use Illuminate\Database\Seeder;

class AdditionalPendingSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('Creating 3 additional pending overtime requests for Branch Test B...');

        // Find the employee 'Admin Test B' created in BranchFilteringTestSeeder
        // We use whereHas to ensure they are in Branch Test B if names are ambiguous, 
        // but looking up by email (via user) or unique name is safer.
        // In BranchFilteringTestSeeder: name='Admin Test B', email='admin_b@test.com'
        
        $employee = Employee::whereHas('user', function($q) {
            $q->where('email', 'admin_b@test.com');
        })->first();

        if (!$employee) {
            $this->command->error("Employee with email 'admin_b@test.com' not found. Please run BranchFilteringTestSeeder first.");
            return;
        }

        for ($i = 1; $i <= 3; $i++) {
            EmployeeOvertime::create([
                'employee_id' => $employee->id,
                'date' => now()->addDays($i)->format('Y-m-d'), // Future dates to distinguish
                'start_time' => '18:00:00',
                'end_time' => '20:00:00',
                'duration_hours' => 2,
                'reason' => "Tambahan Lembur Pending #{$i} for Branch B",
                'status' => EmployeeOvertimeStatusEnum::PENDING,
                'created_at' => now(),
            ]);
        }

        $this->command->info('Successfully created 3 pending overtime requests for Admin Test B.');
    }
}
