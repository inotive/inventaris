<?php

namespace Database\Seeders;

use App\Models\Branch;
use App\Models\Employee;
use App\Models\GeneralSubmission;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Faker\Factory as Faker;

class SubmissionGeneralSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faker = Faker::create('id_ID');

        // Get random employees with their branches
        $employees = Employee::whereHas('branch')->inRandomOrder()->limit(20)->get();
        // Get admin user for approver
        $admin = User::role('Superadmin')->first() ?? User::first();

        foreach ($employees as $employee) {
            // Create pending submissions
            GeneralSubmission::create([
                'employee_id' => $employee->id,
                'branch_id' => $employee->branch_id,
                'title' => $faker->sentence(3),
                'tag' => $faker->randomElements(['IT Support', 'HR', 'General Affair', 'Finance'], rand(1, 3)),
                'note' => $faker->paragraph(),
                'status' => GeneralSubmission::STATUS_PENDING,
                'created_at' => $faker->dateTimeBetween('-1 month', 'now'),
            ]);

            // Create approved submissions
            GeneralSubmission::create([
                'employee_id' => $employee->id,
                'branch_id' => $employee->branch_id,
                'title' => $faker->sentence(3),
                'tag' => $faker->randomElements(['IT Support', 'HR', 'General Affair', 'Finance'], rand(1, 3)),
                'note' => $faker->paragraph(),
                'status' => GeneralSubmission::STATUS_APPROVED,
                'approved_by' => $admin ? $admin->id : null,
                'admin_note' => 'Disetujui',
                'approved_at' => $faker->dateTimeBetween('-1 month', 'now'),
                'created_at' => $faker->dateTimeBetween('-2 months', '-1 month'),
            ]);

            // Create rejected submissions
            GeneralSubmission::create([
                'employee_id' => $employee->id,
                'branch_id' => $employee->branch_id,
                'title' => $faker->sentence(3),
                'tag' => $faker->randomElements(['IT Support', 'HR', 'General Affair', 'Finance'], rand(1, 3)),
                'note' => $faker->paragraph(),
                'status' => GeneralSubmission::STATUS_REJECTED,
                'approved_by' => $admin ? $admin->id : null,
                'admin_note' => 'Ditolak karena tidak lengkap',
                'approved_at' => $faker->dateTimeBetween('-1 month', 'now'), // Reuse approved_at for rejection time if field is used generally for action time
                'created_at' => $faker->dateTimeBetween('-2 months', '-1 month'),
            ]);
        }
    }
}
