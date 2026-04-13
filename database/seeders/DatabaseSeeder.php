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
            RoleSeeder::class,

            // Master data FIRST
            BranchSeeder::class,
            DepartmentSeeder::class,
            ShiftSeeder::class,
            UnitSeeder::class,
            ItemCategorySeeder::class,
            VehicleTypeSeeder::class,

            // Users and Employees (depend on roles and masters)
            UserSeeder::class,
            EmployeeSeeder::class,

            // Inventory and operations
            ItemSeeder::class,
            VehicleSeeder::class,
            VehicleServiceSeeder::class,

            // Checklists and questions
            ChecklistCategorySeeder::class,
            ChecklistSeeder::class,
            QuestionCategorySeeder::class,
            QuestionSeeder::class,
            QuestionOptionSeeder::class,
            // ChecklistHarianSpvSeeder::class,
            // AnswerSeeder::class,
            // ChecklistSignerSeeder::class,

            // Finance and logistics
            MaterialRequestSeeder::class,
            PurchaseRequestSeeder::class,
            PurchaseOrderSeeder::class,
            GoodTransferSeeder::class,
            GoodReceiptSeeder::class,
            GoodIssueSeeder::class,
            ItemStockSeeder::class,

            // Attendance and leaves
            LeaveTypeSeeder::class,
            EmployeeLeaveBalanceSeeder::class,
            // EmployeeLeaveRequestSeeder::class,
            // EmployeeOvertimeSeeder::class,
            // EmployeeDayOffSeeder::class,
            // AttendanceShiftWorkSeeder::class,
            // AttendanceHistorySeeder::class,
            // WorkPrincipleSeeder::class,
            // SalarySlipSeeder::class,
            // AnnouncementSeeder::class,
            // ComplianceSeeder::class,
            // DailyReportSeeder::class,


            // SubmissionSeeder::class,
            // EmployeeOvertimeSeeder::class,

            // Demo/fixtures
            // ItemMovementSeeder::class,
            // InspectionSeeder::class,
            // NotificationSeeder::class,
            // VehicleHistoryOdometerSeeder::class,
            // ReimbursementSeeder::class,

            HKMSBranchDepartmentSeeder::class,
            EmployeePhotoSeeder::class
        ]);
    }
}
