<?php

use Inertia\Inertia;
use App\Models\ItemCategory;
use Illuminate\Support\Facades\Route;
use Illuminate\Foundation\Application;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ItemController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\UnitController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ShiftController;
use App\Http\Controllers\BranchController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\StockInController;
use App\Http\Controllers\VehicleController;
use App\Http\Controllers\CalendarController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\StockOutController;
use App\Http\Controllers\ChecklistController;
use App\Http\Controllers\DepartmentController;
use App\Http\Controllers\GoodIssuesController;
use App\Http\Controllers\InspectionController;
use App\Http\Controllers\StockRecapController;
use App\Http\Controllers\SubmissionController;
use App\Http\Controllers\AbsenceAreaController;
use App\Http\Controllers\GoodReceiptController;
use App\Http\Controllers\VehicleTypeController;
use App\Http\Controllers\GoodTransferController;
use App\Http\Controllers\ItemCategoryController;
use App\Http\Controllers\PurchaseOrderController;
use App\Http\Controllers\SubmissionDebtController;
use App\Http\Controllers\SubmissionReimbursementController;
use App\Http\Controllers\SubmissionSickController;
use App\Http\Controllers\VehicleServiceController;
use App\Http\Controllers\MaterialRequestController;
use App\Http\Controllers\PurchaseRequestController;
use App\Http\Controllers\SubmissionOtherController;
use App\Http\Controllers\SubmissionUsageController;
use App\Http\Controllers\VehicleDocumentsController;
use App\Http\Controllers\Api\v1\GoogleAuthController;
use App\Http\Controllers\ChecklistCategoryController;
use App\Http\Controllers\VehicleInspectionController;
use App\Http\Controllers\SubmissionEmployeeController;
use App\Http\Controllers\SubmissionOvertimeController;
use App\Http\Controllers\AttendanceShiftWorkController;
use App\Http\Controllers\SubmissionAnnualLeaveController;
use App\Http\Controllers\SubmissionProcurementController;
use App\Http\Controllers\VehicleInspectionRecapController;
use App\Http\Controllers\SubmissionDailyReportController;
use App\Http\Controllers\LeaveTypeWebController;
use App\Http\Controllers\SalarySlipController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EmployeePerformanceController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\SubmissionGeneralController;

Route::get('/', function () {
    return redirect()->route('login');
});


Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
])->group(function () {

    Route::get('/profile', [ProfileController::class, 'showDetail'])
        ->name('profile.show');
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::resource('/roles', RoleController::class);
    Route::get('/roles/{role}/stats', [RoleController::class, 'stats'])->name('roles.stats');
    Route::resource('/users', UserController::class);
    Route::put('/users/{user}/verify', [UserController::class, 'verify'])->name('users.verify');
    Route::post('/users/{user}/verify', [UserController::class, 'verify'])->name('users.verify.post');
    Route::resource('/branches', BranchController::class);
    Route::resource('/departments', DepartmentController::class);
    Route::get('/departments/{department}/employees', [DepartmentController::class, 'getEmployees'])->name('departments.employees');
    Route::get('/employees/export', [EmployeeController::class, 'exportData'])->name('employees.export');
    Route::post('/employees/bulk-update', [EmployeeController::class, 'bulkUpdate'])->name('employees.bulk-update');
    Route::post('/employees/bulk-delete', [EmployeeController::class, 'bulkDelete'])->name('employees.bulk-delete');
    Route::resource('/employees', EmployeeController::class);
    Route::put('/employees/{employee}/verify', [EmployeeController::class, 'updateVerify'])->name('employees.updateVerify');
    Route::put('/employees/{employee}/verify-update', [EmployeeController::class, 'verifyAndUpdate'])->name('employees.verifyUpdate');

    // Employee Leave Balance Management
    Route::post('/employees/{employee}/leave-balances', [\App\Http\Controllers\EmployeeLeaveBalanceController::class, 'store'])
        ->name('employees.leave-balances.store');

    // Reports (Laporan Karyawan)
    Route::resource('/reports', ReportController::class);

    // Employee documents upload
    Route::post('/employees/{employee}/documents', [\App\Http\Controllers\EmployeeDocumentController::class, 'store'])->name('employees.documents.store');
    Route::resource('/shifts', ShiftController::class);
    Route::resource('/checklist-categories', ChecklistCategoryController::class);
    Route::resource('/checklists', ChecklistController::class);
    Route::post('/checklists/send-reminder', [ChecklistController::class, 'sendReminder'])->name('checklists.send-reminder');
    Route::resource('/inspections', InspectionController::class)->only(['index', 'show', 'destroy'])->names('inspections');
    Route::post('/inspections/{id}/send-spv-reminder', [InspectionController::class, 'sendSpvReminder'])->name('inspections.send-spv-reminder');
    Route::get('/inspections/{id}/answers', [InspectionController::class, 'answers'])->name('inspections.answers');
    Route::get('/inspections/{id}/export', [InspectionController::class, 'export'])->name('inspections.export');
    Route::resource('/item-categories', ItemCategoryController::class);
    Route::resource('/vehicle-types', VehicleTypeController::class);
    Route::resource('/vehicles', VehicleController::class);
    Route::resource('/vehicle-documents', VehicleDocumentsController::class);
    // Header notification widget endpoint
    Route::get('/notifications/widget', [\App\Http\Controllers\NotificationWidgetController::class, 'index'])->name('notifications.widget');
    Route::resource('/vehicle-inspections', VehicleInspectionController::class);
    Route::resource('/vehicle-inspection-recaps', VehicleInspectionRecapController::class);
    Route::resource('/vehicle-services', VehicleServiceController::class);
    Route::resource('/absence-areas', AbsenceAreaController::class);

    // Specific routes must be defined BEFORE the resource route
    Route::get('/material-requests/requests', [MaterialRequestController::class, 'getMaterialRequests'])->name('material-requests.requests');
    Route::get('/material-requests/items/by-department', [MaterialRequestController::class, 'getItemsByDepartment'])->name('material-requests.items.by-department');
    Route::get('/material-requests/items/by-request', [MaterialRequestController::class, 'getItemsByRequest'])->name('material-requests.items.by-request');
    Route::post('/material-requests/{material_request}/rejected', [MaterialRequestController::class, 'rejected'])->name('material-requests.rejected');
    Route::post('/material-requests/{material_request}/approve', [MaterialRequestController::class, 'approve'])->name('material-requests.approve');
    Route::post('/material-requests/{material_request}/issue', [MaterialRequestController::class, 'issue'])->name('material-requests.issue');
    Route::post('/material-requests/{material_request}/cancel', [MaterialRequestController::class, 'cancel'])->name('material-requests.cancel');

    Route::resource('/material-requests', MaterialRequestController::class);
    Route::resource('/purchase-requests', PurchaseRequestController::class);
    Route::post('/purchase-requests/{purchase_request}/approve', [PurchaseRequestController::class, 'approve'])->name('purchase-requests.approve');
    Route::post('/purchase-requests/{purchase_request}/rejected', [PurchaseRequestController::class, 'rejected'])->name('purchase-requests.rejected');
    Route::post('/purchase-requests/{purchase_request}/cancel', [PurchaseRequestController::class, 'cancel'])->name('purchase-requests.cancel');
    Route::post('/purchase-requests/from-material-request/{material_request}', [PurchaseRequestController::class, 'fromMaterialRequest'])->name('purchase-requests.from-material-request');

    Route::resource('/purchase-orders', PurchaseOrderController::class);
    Route::post('/purchase-orders/{purchase_order}/print', [PurchaseOrderController::class, 'print'])->name('purchase-orders.print');
    Route::get('/purchase-orders/{purchase_order}/print-receipt', [PurchaseOrderController::class, 'printReceipt'])->name('purchase-orders.print-receipt');
    Route::post('/purchase-orders/from-purchase-request/{purchase_request}', [PurchaseOrderController::class, 'fromPurchaseRequest'])->name('purchase-orders.from-purchase-request');
    Route::post('/purchase-orders/{id}/receive', [PurchaseOrderController::class, 'receive'])->name('purchase-orders.receive');
    Route::post('/purchase-orders/{id}/update-receive', [PurchaseOrderController::class, 'updateReceive'])->name('purchase-orders.update-receive');
    Route::post('/purchase-orders/{id}/update-payment', [PurchaseOrderController::class, 'updatePayment'])->name('purchase-orders.update-payment');

    Route::resource('/good-receipts', GoodReceiptController::class);
    Route::group([
        'prefix' => 'good-receipts',
    ], function () {
        Route::get('/load-items/{id}', [GoodReceiptController::class, 'loadItems'])->name('good-receipts.loadItems');
        Route::post('/{good_receipt}/print', [GoodReceiptController::class, 'print'])->name('good-receipts.print');
    });
    Route::resource('/good-transfers', GoodTransferController::class);
    Route::post('/good-transfers/{good_transfer}/receive', [GoodTransferController::class, 'receive'])->name('good-transfers.receive');
    Route::post('/good-transfers/{good_transfer}/cancel', [GoodTransferController::class, 'cancel'])->name('good-transfers.cancel');
    Route::get('/good-transfers/{good_transfer}/print-receipt', [GoodTransferController::class, 'printReceipt'])->name('good-transfers.print-receipt');

    Route::post('/good-issues/{good_issue}/approve', [GoodIssuesController::class, 'approve'])->name('good-issues.approve');
    Route::resource('/good-issues', GoodIssuesController::class);

    Route::resource('/items', ItemController::class);
    Route::resource('/stock-in', StockInController::class);
    Route::resource('/stock-out', StockOutController::class);

    // Announcements (Pengumuman)
    Route::resource('/announcements', \App\Http\Controllers\AnnouncementController::class)
        ->only(['index', 'store', 'update', 'destroy'])
        ->names('announcements');

    // Work Principles (Prinsip & Etos Kerja)
    Route::resource('/work-principles', \App\Http\Controllers\WorkPrincipleController::class)
        ->only(['index', 'store', 'update', 'destroy'])
        ->names('work-principles');

    // Compliance (Tata Tertib Perusahaan)
    Route::resource('/compliance', \App\Http\Controllers\ComplianceController::class)
        ->only(['index', 'store', 'update', 'destroy'])
        ->names('compliance');

    Route::group([
        'prefix' => 'submissions',
    ], function () {
        Route::get('/', [SubmissionController::class, 'index'])->name('submission.index');
        Route::get('/create', [SubmissionController::class, 'create'])->name('create');
        Route::get('/show', [SubmissionController::class, 'show'])->name('submission.submissions.show');
    });


    Route::group([
        'prefix' => 'sick',
    ], function () {
        Route::get('/', [SubmissionSickController::class, 'index'])->name('submission.sick.index');
        Route::get('/{id}', [SubmissionSickController::class, 'show'])->name('submission.sick.show');
        Route::put('/{id}', [SubmissionSickController::class, 'update'])->name('submission.sick.update');
        Route::patch('/{id}/correct', [SubmissionSickController::class, 'correct'])->name('submission.sick.correct');
    });

    Route::group([
        'prefix' => 'annual-leave',
    ], function () {
        Route::get('/', [SubmissionAnnualLeaveController::class, 'index'])->name('submission.leave.index');
        Route::get('/{id}', [SubmissionAnnualLeaveController::class, 'show'])->name('submission.annual-leave.show');
        Route::put('/{id}', [SubmissionAnnualLeaveController::class, 'update'])->name('submission.annual-leave.update');
        Route::patch('/{id}/correct', [SubmissionAnnualLeaveController::class, 'correct'])->name('submission.annual-leave.correct');
    });

    Route::group([
        'prefix' => 'overtime',
    ], function () {
        Route::get('/', [SubmissionOvertimeController::class, 'index'])->name('submission.overtime.index');
        Route::get('/{id}', [SubmissionOvertimeController::class, 'show'])->name('submission.overtime.show');
        Route::put('/{id}', [SubmissionOvertimeController::class, 'update'])->name('submission.overtime.update');
    });

    Route::group([
        'prefix' => 'others',
    ], function () {
        Route::get('/', [SubmissionOtherController::class, 'index'])->name('submission.others.index');
        Route::get('/{id}', [SubmissionOtherController::class, 'show'])->name('submission.others.show');
        Route::put('/{id}', [SubmissionOtherController::class, 'update'])->name('submission.others.update');
        Route::patch('/{id}/correct', [SubmissionOtherController::class, 'correct'])->name('submission.others.correct');
    });



    Route::group([
        'prefix' => 'debt',
    ], function () {
        Route::get('/', [SubmissionDebtController::class, 'index'])->name('submission.debt.index');
        Route::get('/{id}', [SubmissionDebtController::class, 'show'])->name('submission.debt.show');
        Route::put('/{id}', [SubmissionDebtController::class, 'update'])->name('submission.debt.update');
    });

    Route::group([
        'prefix' => 'reimbursement',
    ], function () {
        Route::get('/', [SubmissionReimbursementController::class, 'index'])->name('submission.reimbursement.index');
        Route::get('/{id}', [SubmissionReimbursementController::class, 'show'])->name('submission.reimbursement.show');
        Route::put('/{id}', [SubmissionReimbursementController::class, 'update'])->name('submission.reimbursement.update');
    });

    Route::group([
        'prefix' => 'employee',
    ], function () {
        Route::get('/', [SubmissionDailyReportController::class, 'index'])->name('submission.employee.index');
        Route::post('/', [SubmissionDailyReportController::class, 'store'])->name('submission.employee.store');
        Route::get('/{id}', [SubmissionDailyReportController::class, 'show'])->name('submission.employee.show');
        Route::put('/{id}', [SubmissionDailyReportController::class, 'update'])->name('submission.employee.update');
        Route::delete('/{id}', [SubmissionDailyReportController::class, 'destroy'])->name('submission.employee.destroy');
    });
    Route::group([
        'prefix' => 'general',
    ], function () {
        Route::get('/', [SubmissionGeneralController::class, 'index'])->name('submission.general.index');
        Route::post('/', [SubmissionGeneralController::class, 'store'])->name('submission.general.store');
        Route::get('/{id}', [SubmissionGeneralController::class, 'show'])->name('submission.general.show');
        Route::put('/{id}', [SubmissionGeneralController::class, 'update'])->name('submission.general.update');
        Route::delete('/{id}', [SubmissionGeneralController::class, 'destroy'])->name('submission.general.destroy');
    });

    // Units CRUD
    Route::resource('/units', \App\Http\Controllers\UnitController::class)
        ->only(['index', 'store', 'update', 'destroy']);

    // Vehicles CRUD
    Route::resource('/vehicles', \App\Http\Controllers\VehicleController::class)
        ->only(['index', 'store', 'update', 'destroy'])
        ->names('vehicles');

    // IMPORTANT: Bulk routes MUST be defined BEFORE resource route!
    // Otherwise Laravel interprets "bulk-update" as {id} parameter and returns "Data tidak ditemukan"
    Route::post('/attendance-shift-works/bulk', [AttendanceShiftWorkController::class, 'bulkStore'])->name('shift-works.bulkStore');
    Route::post('/attendance-shift-works/bulk-destroy', [AttendanceShiftWorkController::class, 'bulkDestroy'])->name('shift-works.bulkDestroy');
    Route::put('/attendance-shift-works/bulk-update', [AttendanceShiftWorkController::class, 'bulkUpdate'])->name('shift-works.bulkUpdate');

    Route::resource('/attendance-shift-works', AttendanceShiftWorkController::class)
        ->only(['index', 'create', 'store', 'edit', 'show', 'update', 'destroy'])
        ->names('shift-works');

    // Leave Types (Kategori Izin)
    Route::resource('/leave-types', LeaveTypeWebController::class)
        ->only(['index', 'store', 'update', 'destroy'])
        ->names('leave-types');

    // Positions
    Route::get('/positions', [\App\Http\Controllers\PositionController::class, 'checkExist'])->name('positions.checkExist');
    Route::post('/positions', [\App\Http\Controllers\PositionController::class, 'store'])->name('positions.store');

    // Holiday Calendar
    Route::get('/calendar', [CalendarController::class, 'index'])->name('calendar.index');
    Route::post('/calendar/update-shift', [CalendarController::class, 'updateShift'])->name('calendar.update-shift');

    // Attendance history list
    Route::get('/presences', [\App\Http\Controllers\AttendanceHistoryController::class, 'index'])->name('presences.index');
    Route::get('/presences/{id}', [\App\Http\Controllers\AttendanceHistoryController::class, 'show'])->name('presences.show');
    Route::post('/presences', [\App\Http\Controllers\AttendanceHistoryController::class, 'store'])->name('presences.store');
    Route::put('/presences/{id}', [\App\Http\Controllers\AttendanceHistoryController::class, 'update'])->name('presences.update');
    Route::patch('/presences/{id}/correct-employee', [\App\Http\Controllers\AttendanceHistoryController::class, 'correctEmployee'])->name('presences.correct-employee');
    // Attendance recap page
    Route::get('/attendance-recap', [\App\Http\Controllers\AttendanceRecapController::class, 'index'])->name('attendance-recap.index');
    Route::get('/attendance-recap/departments', [\App\Http\Controllers\AttendanceRecapController::class, 'getDepartments'])->name('attendance-recap.departments');
    Route::get('/attendance-recap/export', [\App\Http\Controllers\AttendanceRecapController::class, 'export'])->name('attendance-recap.export');
    Route::get('/attendance-recap/late-details/{employeeId}', [\App\Http\Controllers\AttendanceRecapController::class, 'getLateDetails'])->name('attendance-recap.late-details');
    Route::get('/attendance-recap/leave-details/{employeeId}', [\App\Http\Controllers\AttendanceRecapController::class, 'getLeaveDetails'])->name('attendance-recap.leave-details');
    Route::get('/attendance-recap/overtime-details/{employeeId}', [\App\Http\Controllers\AttendanceRecapController::class, 'getOvertimeDetails'])->name('attendance-recap.overtime-details');

    // Employee performances page
    Route::get('/employee-performances', [EmployeePerformanceController::class, 'index'])->name('employee-performances.index');
    Route::get('/employee-performances/{employeeId}/score', [EmployeePerformanceController::class, 'getScore'])->name('employee-performances.get-score');
    Route::get('/employee-performances/{employeeId}/score-details', [EmployeePerformanceController::class, 'getScoreDetails'])->name('employee-performances.get-score-details');
    Route::post('/employee-performances', [EmployeePerformanceController::class, 'store'])->name('employee-performances.store');
    Route::put('/employee-performances/{id}', [EmployeePerformanceController::class, 'update'])->name('employee-performances.update');
    Route::post('/employee-performances/calculate-kpi', [EmployeePerformanceController::class, 'calculateKPI'])->name('employee-performances.calculate-kpi');
    // Salary Slips
    Route::get('/salary-slips', [SalarySlipController::class, 'index'])->name('salary-slips.index');
    Route::post('/salary-slips', [SalarySlipController::class, 'store'])->name('salary-slips.store');
    Route::get('/salary-slips/preview', [SalarySlipController::class, 'preview'])->name('salary-slips.preview');
    Route::get('/salary-slips/download', [SalarySlipController::class, 'download'])->name('salary-slips.download');
    Route::delete('/salary-slips', [SalarySlipController::class, 'destroy'])->name('salary-slips.destroy');
    // Realtime absen (camera/check-in)
    Route::get('/presence-check', [\App\Http\Controllers\PresenceController::class, 'index'])->name('presence.check');
    // Alias for sidebar: realtime-presences.index
    Route::get('/realtime-presences', [\App\Http\Controllers\PresenceController::class, 'index'])->name('realtime-presences.index');
    Route::get('/presences/get-information-employee', [\App\Http\Controllers\PresenceController::class, 'getInformationEmployee'])->name('presences.getInformationEmployee');

    // Web logout for Inertia (used by router.post(route('logout')))
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
});

Route::middleware(['web'])->group(function () {
    Route::get('/stock-recap', [StockRecapController::class, 'index'])->name('stock-recap.index');
    Route::get('/api/stock-recap/detail', [StockRecapController::class, 'detail'])->name('stock-recap.detail');
});

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'login'])->name('login');
    Route::post('/login', [AuthController::class, 'auth'])->name('auth');
});

// Google Login
// Route::get('/login/google', [GoogleAuthController::class, 'redirectToGoogle'])->name('google.login');

// Route::get('/login/google/callback', [GoogleAuthController::class, 'loginGoogleApi'])
//     ->name('google.callback');

// Signout for SimpleAuth
// Route::middleware(['auth:sanctum', config('jetstream.auth_session')])->group(function () {
//     Route::post('/signout', [SimpleAuthController::class, 'logout'])->name('signout');
// });
