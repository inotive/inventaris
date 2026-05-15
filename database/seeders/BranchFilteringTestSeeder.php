<?php

namespace Database\Seeders;

use App\Enums\EmployeeOvertimeStatusEnum;
use App\Models\Branch;
use App\Models\Department;
use App\Models\Employee;
use App\Models\EmployeeOvertime;
use App\Models\MaterialRequest;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class BranchFilteringTestSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('Starting Branch Filtering Test Seeder...');

        // 1. Create Test Branches
        $branchA = Branch::firstOrCreate(
            ['email' => 'testa@example.com'],
            [
                'name' => 'Branch Test A',
                'region' => 'Test Region',
                'contact' => '0000000001',
                'address' => 'Test Address A',
            ]
        );

        $branchB = Branch::firstOrCreate(
            ['email' => 'testb@example.com'],
            [
                'name' => 'Branch Test B',
                'region' => 'Test Region',
                'contact' => '0000000002',
                'address' => 'Test Address B',
            ]
        );

        $this->command->info("Created Branches: {$branchA->name} (ID: {$branchA->id}), {$branchB->name} (ID: {$branchB->id})");

        // 2. Get dependencies
        $department = Department::firstOrCreate(['name' => 'Test Dept']);
        $position = \App\Models\Position::firstOrCreate(['name' => 'Test Pos']);

        // 3. Create Admin Users for each Branch
        $adminA = $this->createAdminUser('admin_test_a', 'Admin Test A', 'admin_a@test.com', $branchA, $department, $position);
        $adminB = $this->createAdminUser('admin_test_b', 'Admin Test B', 'admin_b@test.com', $branchB, $department, $position);

        // 4. Create Submissions (Overtime) for each Admin (so they have their own data)
        $this->createOvertime($adminA->employee, EmployeeOvertimeStatusEnum::PENDING);
        
        // Create 3 Pending items for Branch B as requested
        for ($i = 1; $i <= 3; $i++) {
             $this->createOvertime($adminB->employee, EmployeeOvertimeStatusEnum::PENDING, "Pending Overtime B #{$i}");
        }
        
        // 5. Create Cross Data (Data in Branch B that Admin A should NOT see)
        // Admin B creates more data
        $this->createOvertime($adminB->employee, EmployeeOvertimeStatusEnum::APPROVED, 'Approved Overtime Branch B');
        $this->createOvertime($adminB->employee, EmployeeOvertimeStatusEnum::REJECTED, 'Rejected Overtime Branch B');

        // Admin A creates more data
        $this->createOvertime($adminA->employee, EmployeeOvertimeStatusEnum::APPROVED, 'Approved Overtime Branch A');

        // 6. Create Material Requests (on_progress) to test the Pending Count View
        // Create one for Branch A
        MaterialRequest::create([
            'request_no' => 'MR-TEST-A-001-' . uniqid(),
            'department_id' => $department->id,
            'requested_by' => $adminA->employee->id,
            'requested_at' => now(),
            'requirement' => 'Test Material A',
            'status' => 'on_progress', // Should be counted in view
        ]);

        // Create one for Branch B
        MaterialRequest::create([
            'request_no' => 'MR-TEST-B-001-' . uniqid(),
            'department_id' => $department->id,
            'requested_by' => $adminB->employee->id,
            'requested_at' => now(),
            'requirement' => 'Test Material B',
            'status' => 'on_progress', // Should be counted in view
        ]);

        $this->command->info("Test Data Created Successfully.");
        $this->command->info("Login as admin_a@test.com / 123123 to verify you ONLY see Branch A data.");
        $this->command->info("Login as admin_b@test.com / 123123 to verify you ONLY see Branch B data.");
    }

    private function createAdminUser($username, $name, $email, $branch, $department, $position)
    {
        $user = User::firstOrCreate(
            ['email' => $email],
            [
                'name' => $name,
                'username' => $username,
                'password' => Hash::make('123123'),
            ]
        );

        // Assign 'Admin' role using Spatie Permission if available, assuming role exists
        // We'll try to assign 'Admin' and 'Supervisor' to test different roles if needed, 
        // but let's stick to 'Admin' as requested.
        try {
            $user->assignRole('Admin');
        } catch (\Throwable $e) {
            $this->command->warn("Could not assign role 'Admin' to user {$username}. Ensure the role exists.");
        }

        $employee = Employee::firstOrCreate(
            ['user_id' => $user->id],
            [
                'name' => $name,
                'department_id' => $department->id,
                'branch_id' => $branch->id,
                'position_id' => $position->id,
                'status' => 'Tetap',
                'working_start_date' => now(),
                'salary' => 5000000,
            ]
        );

        return $user;
    }

    private function createOvertime($employee, $status, $reason = 'Test Overtime')
    {
        EmployeeOvertime::create([
            'employee_id' => $employee->id,
            'date' => now()->format('Y-m-d'),
            'start_time' => '17:00:00',
            'end_time' => '19:00:00',
            'duration_hours' => 2,
            'reason' => $reason,
            'status' => $status,
            'created_at' => now(),
        ]);
    }
}
