<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\User;
use App\Models\Employee;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class RoleAndUserWarehouseSeeder extends Seeder
{
    public function run(): void
    {
        // Define roles
        $superadminRole = Role::firstOrCreate(['name' => 'Superadmin', 'guard_name' => 'web']);
        $superadminRole->syncPermissions(Permission::all());

        $pusatRole = Role::firstOrCreate(['name' => 'Admin Gudang Pusat', 'guard_name' => 'web']);
        $cabangUtamaRole = Role::firstOrCreate(['name' => 'Admin Cabang Utama', 'guard_name' => 'web']);
        $cabangRole = Role::firstOrCreate(['name' => 'Admin Gudang Cabang', 'guard_name' => 'web']);

        // Give global permissions to Pusat
        $pusatPermissions = [
            'dashboard.view', 'dashboard.view_global',
            'good_transfers.view', 'good_transfers.create', 'good_transfers.edit', 'good_transfers.delete', 'good_transfers.view_global', 'good_transfers.create_global',
            'good_receipts.view', 'good_receipts.create', 'good_receipts.edit', 'good_receipts.delete', 'good_receipts.view_global', 'good_receipts.create_global',
            'good_issues.view', 'good_issues.create', 'good_issues.edit', 'good_issues.delete', 'good_issues.view_global', 'good_issues.create_global', 'good_issues.approve',
            'material_requests.view', 'material_requests.create', 'material_requests.edit', 'material_requests.delete', 'material_requests.view_global', 'material_requests.create_global', 'material_requests.approve',
            'purchase_requests.view', 'purchase_requests.create', 'purchase_requests.edit', 'purchase_requests.delete', 'purchase_requests.view_global', 'purchase_requests.create_global', 'purchase_requests.approve',
            'purchase_orders.view', 'purchase_orders.create', 'purchase_orders.edit', 'purchase_orders.delete', 'purchase_orders.view_global', 'purchase_orders.create_global', 'purchase_orders.approve',
            'stock_recap.view', 'stock_recap.view_global', 'stock_recap.adjust',
            'stock_in.view', 'stock_in.view_global', 'stock_in.create', 'stock_in.edit', 'stock_in.delete',
            'stock_out.view', 'stock_out.view_global', 'stock_out.create', 'stock_out.edit', 'stock_out.delete',
            'items.view', 'items.view_global', 'items.create', 'items.edit', 'items.delete',
        ];
        $pusatRole->syncPermissions($pusatPermissions);

        // Give standard permissions + view_global to Cabang Utama
        $cabangUtamaPermissions = [
            'dashboard.view', 'dashboard.view_global',
            'good_transfers.view', 'good_transfers.create', 'good_transfers.edit', 'good_transfers.delete', 'good_transfers.view_global',
            'good_receipts.view', 'good_receipts.create', 'good_receipts.edit', 'good_receipts.delete', 'good_receipts.view_global',
            'good_issues.view', 'good_issues.create', 'good_issues.edit', 'good_issues.delete', 'good_issues.view_global',
            'material_requests.view', 'material_requests.create', 'material_requests.edit', 'material_requests.delete', 'material_requests.view_global',
            'purchase_requests.view', 'purchase_requests.create', 'purchase_requests.edit', 'purchase_requests.delete', 'purchase_requests.view_global',
            'stock_recap.view', 'stock_recap.view_global',
            'stock_in.view', 'stock_in.view_global',
            'stock_out.view', 'stock_out.view_global',
        ];
        $cabangUtamaRole->syncPermissions($cabangUtamaPermissions);

        // Give standard permissions to Cabang (no global, no stock in/out view, no adjust)
        $cabangPermissions = [
            'dashboard.view',
            'good_transfers.view', 'good_transfers.create', 'good_transfers.edit', 'good_transfers.delete',
            'good_receipts.view', 'good_receipts.create', 'good_receipts.edit', 'good_receipts.delete',
            'good_issues.view', 'good_issues.create', 'good_issues.edit', 'good_issues.delete',
            'material_requests.view', 'material_requests.create', 'material_requests.edit', 'material_requests.delete',
            'stock_recap.view'
        ];
        $cabangRole->syncPermissions($cabangPermissions);

        // Create Accounts (Users + Employees)
        // Ensure to run this AFTER BranchSeeder so branches 11 and 12 exist!
        $users = [
            [
                'name' => 'Superadmin',
                'username' => 'superadmin',
                'email' => 'superadmin@perusahaan.co.id',
                'password' => Hash::make('password'),
                'role' => 'Superadmin',
                'branch_id' => 12, // Gudang Pusat
            ],
            [
                'name' => 'Admin Gudang Pusat',
                'username' => 'admin.pusat',
                'email' => 'adminpusat@perusahaan.co.id',
                'password' => Hash::make('password'),
                'role' => 'Admin Gudang Pusat',
                'branch_id' => 12, // Gudang Pusat
            ],
            [
                'name' => 'Admin Cabang Utama',
                'username' => 'admin.utama',
                'email' => 'adminutama@perusahaan.co.id',
                'password' => Hash::make('password'),
                'role' => 'Admin Cabang Utama',
                'branch_id' => 11, // Gudang Utama Cabang Sangatta
            ],
            [
                'name' => 'Admin Gudang Sekerat',
                'username' => 'admin.sekerat',
                'email' => 'adminsekerat@perusahaan.co.id',
                'password' => Hash::make('password'),
                'role' => 'Admin Gudang Cabang',
                'branch_id' => 1, // Gudang Cabang Sekerat
            ]
        ];

        foreach ($users as $userData) {
            $user = User::firstOrCreate(
                ['email' => $userData['email']],
                [
                    'name' => $userData['name'],
                    'username' => $userData['username'],
                    'password' => $userData['password'],
                ]
            );

            $user->syncRoles($userData['role']);

            $department = \App\Models\Department::firstOrCreate(
                ['name' => 'Gudang', 'branch_id' => $userData['branch_id']],
                ['description' => 'Departemen Gudang']
            );

            Employee::updateOrCreate(
                ['user_id' => $user->id],
                [
                    'name' => $user->name,
                    'branch_id' => $userData['branch_id'],
                    'department_id' => $department->id,
                    'status' => 'Tetap',
                    'working_start_date' => now()->toDateString(),
                ]
            );
        }
    }
}
