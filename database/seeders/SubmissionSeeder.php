<?php

namespace Database\Seeders;

use App\Enums\SubmissionStatusEnum;
use App\Enums\SubmissionTypeEnum;
use App\Models\Branch;
use App\Models\Employee;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SubmissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faker = \Faker\Factory::create('id_ID'); // Locale Indonesia

        $Employee = Employee::pluck('id')->toArray();
        $Branch = Branch::pluck('id')->toArray();
        for ($i = 0; $i < 100; $i++) {
            \App\Models\Submission::create([
                'employee_id' => $Employee[array_rand($Employee)],
                'branch_id' => $Branch[array_rand($Branch)],
                'status' => $faker->randomElement(SubmissionStatusEnum::cases()),
                'type' => $faker->randomElement(SubmissionTypeEnum::cases()),
                'submission_date' => $faker->dateTimeBetween('-1 years', 'now')->format('Y-m-d'),
            ]);
        }
    }
}
