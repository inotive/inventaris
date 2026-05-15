<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Role;
use Spatie\Permission\Models\Permission;

class RolePermissionSeeder extends Seeder
{
    public function run()
    {
        // Assign semua permission 'roles.*' ke role tertentu
        $rolesToAssign = ['Superadmin', 'Admin']; // Sesuaikan dengan role Anda
        
        foreach ($rolesToAssign as $roleName) {
            $role = Role::where('name', $roleName)->first();
            
            if ($role) {
                // Berikan semua permission roles
                $role->givePermissionTo([
                    'roles.view',
                    'roles.create',
                    'roles.edit',
                    'roles.delete',
                ]);
            }
        }
    }
}