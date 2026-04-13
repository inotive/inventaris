<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class MasterPenyimpananSeeder extends Seeder
{
    /**
     * Seeder khusus untuk master data + modul Penyimpanan.
     * Tidak menyertakan data HR, kehadiran, kendaraan, checklist, dll.
     */
    public function run(): void
    {
        $this->call([
            // Akses & role
            PermissionSeeder::class,
            RoleSeeder::class,

            // Master data
            BranchSeeder::class,
            DepartmentSeeder::class,
            PositionSeeder::class,
            UnitSeeder::class,
            ItemCategorySeeder::class,

            // User (superadmin + admin saja)
            UserSeeder::class,

            // Penyimpanan (barang & stok)
            ItemSeeder::class,
        ]);
    }
}
