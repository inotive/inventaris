<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Employee;
use App\Models\Position;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $users = [
            [
                'user' => [
                    'name'     => 'Superadmin',
                    'username' => 'superadmin',
                    'email'    => 'superadmin@perusahaan.co.id',
                    'password' => Hash::make('password'),
                ],
                'role'          => 'Superadmin',
                'branch_id'     => 1,
                'department_id' => 1,
                'position'      => 'Direktur Utama',
            ],
            [
                'user' => [
                    'name'     => 'Admin Gudang',
                    'username' => 'admin',
                    'email'    => 'admin@perusahaan.co.id',
                    'password' => Hash::make('password'),
                ],
                'role'          => 'Admin',
                'branch_id'     => 1,
                'department_id' => 9,
                'position'      => 'Admin',
            ],
            [
                'user' => [
                    'name'     => 'Budi Santoso',
                    'username' => 'budi.santoso',
                    'email'    => 'budi@perusahaan.co.id',
                    'password' => Hash::make('password'),
                ],
                'role'          => 'Staff',
                'branch_id'     => 1,
                'department_id' => 5,
                'position'      => 'Kepala Gudang',
            ],
            [
                'user' => [
                    'name'     => 'Siti Rahayu',
                    'username' => 'siti.rahayu',
                    'email'    => 'siti@perusahaan.co.id',
                    'password' => Hash::make('password'),
                ],
                'role'          => 'Staff',
                'branch_id'     => 1,
                'department_id' => 5,
                'position'      => 'Staff Gudang',
            ],
            [
                'user' => [
                    'name'     => 'Agus Prasetyo',
                    'username' => 'agus.prasetyo',
                    'email'    => 'agus@perusahaan.co.id',
                    'password' => Hash::make('password'),
                ],
                'role'          => 'Staff',
                'branch_id'     => 2,
                'department_id' => 11,
                'position'      => 'Staff Gudang',
            ],
            [
                'user' => [
                    'name'     => 'Dewi Lestari',
                    'username' => 'dewi.lestari',
                    'email'    => 'dewi@perusahaan.co.id',
                    'password' => Hash::make('password'),
                ],
                'role'          => 'Staff',
                'branch_id'     => 2,
                'department_id' => 14,
                'position'      => 'Staff Administrasi',
            ],
            [
                'user' => [
                    'name'     => 'Rudi Hermawan',
                    'username' => 'rudi.hermawan',
                    'email'    => 'rudi@perusahaan.co.id',
                    'password' => Hash::make('password'),
                ],
                'role'          => 'Staff',
                'branch_id'     => 3,
                'department_id' => 15,
                'position'      => 'Supervisor Gudang',
            ],
            [
                'user' => [
                    'name'     => 'Manager Operasional',
                    'username' => 'manager.ops',
                    'email'    => 'manager@perusahaan.co.id',
                    'password' => Hash::make('password'),
                ],
                'role'          => 'Admin',
                'branch_id'     => 1,
                'department_id' => 1,
                'position'      => 'Manajer Operasional',
            ],
        ];

        foreach ($users as $data) {
            $u = User::create($data['user']);
            $u->syncRoles($data['role']);

            $position = Position::where('name', $data['position'])->first();

            Employee::create([
                'user_id'            => $u->id,
                'name'               => $data['user']['name'],
                'branch_id'          => $data['branch_id'],
                'department_id'      => $data['department_id'],
                'position_id'        => $position?->id,
                'status'             => 'Tetap',
                'working_start_date' => now()->subYear()->toDateString(),
                'created_at'         => now(),
                'updated_at'         => now(),
            ]);

            $this->command->info('User ' . $u->name . ' created.');
        }
    }
}
