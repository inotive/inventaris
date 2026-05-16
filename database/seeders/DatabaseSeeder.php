<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;

use App\Models\EmployeeLeaveApproval;
use App\Models\ItemMovement;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            // Access control
            PermissionSeeder::class,
            // RoleSeeder::class,

            // Master data FIRST
            BranchSeeder::class,
            ItemWarehouseSeeder::class,
            // DepartmentSeeder::class,
            // ShiftSeeder::class,
            // UnitSeeder::class,
            // ItemCategorySeeder::class,
            // VehicleTypeSeeder::class,

            // Users and Employees (depend on roles and masters)
            RoleAndUserWarehouseSeeder::class,
            // UserSeeder::class,
            // EmployeeSeeder::class,

            // Inventory and operations
            // ItemSeeder::class,
            // VehicleSeeder::class,
            // VehicleServiceSeeder::class,

            // Checklists and questions
            // ChecklistCategorySeeder::class,
            // ChecklistSeeder::class,
            // QuestionCategorySeeder::class,
            // QuestionSeeder::class,
            // QuestionOptionSeeder::class,

            // Finance and logistics
            // MaterialRequestSeeder::class,
            // PurchaseRequestSeeder::class,
            // PurchaseOrderSeeder::class,
            // GoodTransferSeeder::class,
            // GoodReceiptSeeder::class,
            // GoodIssueSeeder::class,
            // ItemStockSeeder::class,

            // Attendance and leaves
            // LeaveTypeSeeder::class,
            // EmployeeLeaveBalanceSeeder::class,
            // HKMSBranchDepartmentSeeder::class,
            // EmployeePhotoSeeder::class
        ]);
    }
}
