<?php

namespace Database\Seeders;

use App\Models\Branch;
use App\Models\Department;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DepartmentSeeder extends Seeder
{
    public function run(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        Department::truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $now = now();
        $branches = Branch::all();

        if ($branches->isEmpty()) {
            $this->call(BranchSeeder::class);
            $branches = Branch::all();
        }

        $departments = [
            // Branch 1: Gudang Utama (Jakarta)
            ['name' => 'Direksi', 'branch_id' => 1],
            ['name' => 'Keuangan', 'branch_id' => 1],
            ['name' => 'HRD', 'branch_id' => 1],
            ['name' => 'IT', 'branch_id' => 1],
            ['name' => 'Gudang', 'branch_id' => 1],
            ['name' => 'Logistik', 'branch_id' => 1],
            ['name' => 'Pengiriman', 'branch_id' => 1],
            ['name' => 'Procurement', 'branch_id' => 1],
            ['name' => 'Admin', 'branch_id' => 1],
            ['name' => 'Security', 'branch_id' => 1],

            // Branch 2: Cabang Bandung
            ['name' => 'Gudang', 'branch_id' => 2],
            ['name' => 'Logistik', 'branch_id' => 2],
            ['name' => 'Pengiriman', 'branch_id' => 2],
            ['name' => 'Admin', 'branch_id' => 2],

            // Branch 3: Cabang Surabaya
            ['name' => 'Gudang', 'branch_id' => 3],
            ['name' => 'Logistik', 'branch_id' => 3],
            ['name' => 'Pengiriman', 'branch_id' => 3],
            ['name' => 'Admin', 'branch_id' => 3],

            // Branch 4: Cabang Medan
            ['name' => 'Gudang', 'branch_id' => 4],
            ['name' => 'Logistik', 'branch_id' => 4],
            ['name' => 'Admin', 'branch_id' => 4],

            // Branch 5: Cabang Makassar
            ['name' => 'Gudang', 'branch_id' => 5],
            ['name' => 'Logistik', 'branch_id' => 5],
            ['name' => 'Admin', 'branch_id' => 5],
        ];

        foreach ($departments as &$dept) {
            $dept['status'] = true;
            $dept['created_at'] = $now;
            $dept['updated_at'] = $now;
        }

        Department::insert($departments);
    }
}
