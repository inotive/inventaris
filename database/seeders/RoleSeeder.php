<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Role::firstOrCreate(['name' => 'Public', 'guard_name' => 'web'], [
            'leave_quota_per_year' => 0,
            'loan_quota' => 0
        ]);

        Role::firstOrCreate(['name' => 'User', 'guard_name' => 'web'], [
            'leave_quota_per_year' => 12,
            'loan_quota' => 1000000
        ]);

        $staff = Role::firstOrCreate(['name' => 'Staff', 'guard_name' => 'web'], [
            'leave_quota_per_year' => 12,
            'loan_quota' => 2000000
        ]);
        $staffPermissions = Permission::whereIn('name', [
            'dashboard.view',
            // Transaksi Barang
            'material_requests.view',
            'purchase_requests.view',
            'purchase_orders.view',
            'good_transfers.view',
            'good_receipts.view',
            'good_issues.view',
            // Penyimpanan
            'items.view',
            'stock_in.view',
            'stock_out.view',
            'stock_recap.view',
        ])->get();
        $staff->syncPermissions($staffPermissions);

        $superadmin = Role::firstOrCreate(['name' => 'Superadmin', 'guard_name' => 'web'], [
            'leave_quota_per_year' => 24,
            'loan_quota' => 10000000
        ]);
        $superadmin->syncPermissions(Permission::all());

        $admin = Role::firstOrCreate(['name' => 'Admin', 'guard_name' => 'web'], [
            'leave_quota_per_year' => 18,
            'loan_quota' => 5000000
        ]);
        $adminPermissions = Permission::whereIn('name', [
            'dashboard.view',
            // Transaksi Barang - full akses
            'material_requests.view', 'material_requests.create', 'material_requests.edit', 'material_requests.delete', 'material_requests.approve',
            'purchase_requests.view', 'purchase_requests.create', 'purchase_requests.edit', 'purchase_requests.delete', 'purchase_requests.approve',
            'purchase_orders.view', 'purchase_orders.create', 'purchase_orders.edit', 'purchase_orders.delete', 'purchase_orders.approve',
            'good_transfers.view', 'good_transfers.create', 'good_transfers.edit', 'good_transfers.delete', 'good_transfers.approve',
            'good_receipts.view', 'good_receipts.create', 'good_receipts.edit', 'good_receipts.delete',
            'good_issues.view', 'good_issues.create', 'good_issues.edit', 'good_issues.delete', 'good_issues.approve',
            // Penyimpanan
            'items.view', 'items.create', 'items.edit', 'items.delete',
            'stock_in.view', 'stock_in.create', 'stock_in.edit', 'stock_in.delete',
            'stock_out.view', 'stock_out.create', 'stock_out.edit', 'stock_out.delete',
            'stock_recap.view',
            // Konfigurasi
            'branches.view', 'branches.create', 'branches.edit', 'branches.delete',
            'departments.view', 'departments.create', 'departments.edit', 'departments.delete',
            'users.view', 'users.create', 'users.edit', 'users.delete',
            'roles.view', 'roles.create', 'roles.edit', 'roles.delete',
            'item_categories.view', 'item_categories.create', 'item_categories.edit', 'item_categories.delete',
            'units.view', 'units.create', 'units.edit', 'units.delete',
        ])->get();
        $admin->syncPermissions($adminPermissions);
    }
}
