<?php

namespace Database\Seeders;

use App\Models\Employee;
use App\Models\User;
use App\Models\Branch;
use App\Models\Department;
use App\Models\Shift;
use Spatie\Permission\Models\Permission;
use Faker\Factory as Faker;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;

class EmployeeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faker = Faker::create('id_ID'); // Locale Indonesia

        // Preload master IDs for randomization
        $branchIds = Branch::pluck('id')->all();
        $departmentIds = Department::pluck('id')->all();
        $shiftIds = Shift::pluck('id')->all();

        // 1) Ensure each existing user has an employee record linked via user_id
        $users = User::where('id', '<', 3)->get();
        foreach ($users as $user) {
            $emp = Employee::firstOrCreate(
                ['user_id' => $user->id],
                [
                    'name'        => $user->name ?? $faker->name,
                    'contact'     => $faker->phoneNumber,
                    'address'     => $faker->address,
                    'status'      => $faker->randomElement(['Tetap', 'Kontrak', 'Magang']),
                    'gender'      => $faker->randomElement(['Laki-laki', 'Perempuan']),
                    'birthplace'  => $faker->city,
                    'religion'    => $faker->randomElement(['Islam', 'Kristen', 'Katolik', 'Hindu', 'Buddha', 'Konghucu']),
                    'nik'         => $faker->nik(),
                ]
            );

            // Randomly fill foreign keys if empty
            $emp->branch_id = $emp->branch_id ?: ($branchIds ? $branchIds[array_rand($branchIds)] : null);
            $emp->department_id = $emp->department_id ?: ($departmentIds ? $departmentIds[array_rand($departmentIds)] : null);
            // $emp->permission_id = $emp->permission_id ?: ($permissionIds ? $permissionIds[array_rand($permissionIds)] : null);
            $emp->shift_id = $emp->shift_id ?: ($shiftIds ? $shiftIds[array_rand($shiftIds)] : null);
            // position_id left null (no positions master yet)
            if (empty($emp->working_start_date)) {
                $emp->working_start_date = Carbon::now()->subDays(rand(30, 2000))->toDateString();
            }
            if (empty($emp->salary)) {
                $emp->salary = $faker->numberBetween(2000000, 8000000);
            }
            $emp->save();
        }

        // // for ($i = 0; $i < 100; $i++) {

        // //     $user = User::create([
        // //         'name' => $faker->name,
        // //         'username' => $faker->username,
        // //         'email' => $faker->email,
        // //         'password' => Hash::make('123123'),
        // //     ]);

        // //     Employee::create([
        // //         'user_id'     => $user->id,
        // //         'name'        => $user->name,
        // //         'contact'     => $faker->phoneNumber,
        // //         'address'     => $faker->address,
        // //         'status'      => $faker->randomElement(['Tetap', 'Kontrak', 'Magang']),
        // //         'gender'      => $faker->randomElement(['Laki-laki', 'Perempuan']),
        // //         'birthplace'  => $faker->city,
        // //         'religion'    => $faker->randomElement(['Islam', 'Kristen', 'Katolik', 'Hindu', 'Buddha', 'Konghucu']),
        // //         'nik'         => $faker->nik(),
        // //     ]);
        // // }

        // // 2) Backfill ALL existing employees to ensure foreign keys are set
        // $all = Employee::all();
        // foreach ($all as $emp) {
        //     $changed = false;
        //     if (!$emp->branch_id && $branchIds) {
        //         $emp->branch_id = $branchIds[array_rand($branchIds)];
        //         $changed = true;
        //     }
        //     if (!$emp->department_id && $departmentIds) {
        //         $emp->department_id = $departmentIds[array_rand($departmentIds)];
        //         $changed = true;
        //     }
        //     // if (!$emp->permission_id && $permissionIds) { $emp->permission_id = $permissionIds[array_rand($permissionIds)]; $changed = true; }
        //     if (!$emp->shift_id && $shiftIds) {
        //         $emp->shift_id = $shiftIds[array_rand($shiftIds)];
        //         $changed = true;
        //     }
        //     if (empty($emp->working_start_date)) {
        //         $emp->working_start_date = Carbon::now()->subDays(rand(30, 2000))->toDateString();
        //         $changed = true;
        //     }
        //     if (empty($emp->salary)) {
        //         $emp->salary = $faker->numberBetween(2000000, 8000000);
        //         $changed = true;
        //     }
        //     if ($changed) {
        //         $emp->save();
        //     }
        // }

        // // 2) Seed additional random employees (unassigned to users) if needed
        // for ($i = 0; $i < 1000; $i++) {

        //     $user = User::create([
        //         'name' => $faker->name,
        //         'username' => $faker->username,
        //         'email' => $faker->email,
        //         'password' => Hash::make('123123'),
        //     ]);

        //     Employee::create([
        //         'user_id'     => $user->id,
        //         'name'        => $user->name,
        //         'contact'     => $faker->phoneNumber,
        //         'address'     => $faker->address,
        //         'status'      => $faker->randomElement(['Tetap', 'Kontrak', 'Magang']),
        //         'gender'      => $faker->randomElement(['Laki-laki', 'Perempuan']),
        //         'birthplace'  => $faker->city,
        //         'religion'    => $faker->randomElement(['Islam', 'Kristen', 'Katolik', 'Hindu', 'Buddha', 'Konghucu']),
        //         'nik'         => $faker->nik(),
        //     ]);
        // }
    }
}
