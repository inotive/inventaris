<?php

use App\Http\Controllers\AnnouncementController;
use App\Http\Controllers\Api\v1\AuthController;
use App\Http\Controllers\Api\v1\ChecklistCategoryController;
use App\Http\Controllers\Api\v1\ChecklistController;
use App\Http\Controllers\Api\v1\DepartmentController;
use App\Http\Controllers\Api\v1\EmployeeController;
use App\Http\Controllers\Api\v1\EmployeeLeaveRequestController;
use App\Http\Controllers\Api\v1\EmployeeOvertimeController;
use App\Http\Controllers\Api\v1\LeaveTypeController;
use App\Http\Controllers\Api\v1\PresenceController;
use App\Http\Controllers\Api\v1\VehicleController;
use App\Http\Controllers\Api\v1\AttendanceController;
use App\Http\Controllers\Api\v1\BranchController;
use App\Http\Controllers\Api\v1\FinancialInformationController;
use App\Http\Controllers\Api\v1\ReportController;
use App\Http\Controllers\Api\v1\EmployeeLeaveBalanceController;
use App\Http\Controllers\Api\v1\ItemController;
use App\Http\Controllers\Api\v1\ProfileController;
use App\Http\Controllers\Api\v1\GoogleAuthController;
use App\Http\Controllers\Api\v1\ForgotPasswordController;
use App\Http\Controllers\Api\v1\InspectionController;
use App\Http\Controllers\Api\v1\WorkPrincipleController;
use App\Http\Controllers\Api\v1\NotificationController;
use App\Http\Controllers\Api\v1\EmployeePerformanceController;
use App\Http\Controllers\Api\v1\AnnouncementController as ApiAnnouncementController;
use App\Http\Controllers\Api\v1\CalenderController;
use App\Http\Controllers\Api\v1\SubmissionLoanController;
use App\Http\Controllers\Api\v1\SubmissionProcurementController as ApiSubmissionProcurementController;
use App\Http\Controllers\Api\v1\SubmissionUsageController as ApiSubmissionUsageController;
use App\Http\Controllers\Api\v1\VehicleOdometerHistoryController;
use App\Http\Controllers\Api\v1\UploadController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ComplianceController;
use App\Http\Controllers\Api\v1\StockRecapController as ApiStockRecapController;
use App\Http\Controllers\Api\v1\ComplianceController as ApiComplianceController;
use App\Http\Controllers\Api\v1\SubmissionGeneralController as ApiSubmissionGeneralController;

// --- Auth ---
Route::controller(AuthController::class)->prefix('/v1/auth')->group(function () {
    Route::post('/login', 'login');
    Route::post('/logout', 'logout')->middleware('auth:sanctum');
    Route::post('/update-password', 'updatePassword')->middleware('auth:sanctum');
});

Route::controller(GoogleAuthController::class)->prefix('/v1/auth')->group(function () {
    Route::post('/google', 'checkAndLoginGoogle');
    Route::post('/register', 'register');
});

Route::controller(ForgotPasswordController::class)->prefix('/v1/auth')->group(function () {
    Route::post('/forgot-password', 'forgotPassword');
    Route::post('/reset-password', 'resetPassword');
});

// --- Public Routes ---
Route::prefix('/v1')->group(function () {
    // Leave Types (for web access)
    Route::get('/leave-types', [LeaveTypeController::class, 'index']);

    // Branches (for registration)
    Route::controller(BranchController::class)->prefix('/branchs')->group(function () {
        Route::get('/list-branch', [BranchController::class, 'allBranch']);
    });

    // Departments by branch (for registration)
    Route::get('/departments/{branchId}', [ProfileController::class, 'getDepartmentsByBranch']);

    // Items
    Route::get('/shifts', [ProfileController::class, 'getShifts']);
});

Route::post('/attendance-employee', [\App\Http\Controllers\PresenceController::class, 'attendaceEmployee'])->name('attendance.employee');
// --- Profile ---
Route::middleware('auth:sanctum')->prefix('/v1')->group(function () {
    Route::controller(ProfileController::class)->prefix('/profile')->group(function () {
        Route::get('/', [ProfileController::class, 'index']);
        Route::get('/{id}', [ProfileController::class, 'show']);
        Route::put('/', 'update');
        Route::post('/', 'update'); // Support POST for file uploads

    });

    Route::post('/attendance-employee-barcode', [\App\Http\Controllers\PresenceController::class, 'attendaceEmployeeBarcode'])->name('attendance.employee.barcode');


    // Salary slips for mobile app
    Route::get('/salary-slips', [\App\Http\Controllers\Api\v1\SalarySlipController::class, 'index']);
    Route::get('/salary-slips/{id}/download', [\App\Http\Controllers\Api\v1\SalarySlipController::class, 'download']);

    // Shift assignments (attendance_shift_works)
    Route::get('/shift-works', [\App\Http\Controllers\Api\v1\ShiftWorkController::class, 'index']);

    Route::controller(ChecklistController::class)
        ->prefix('/checklists')
        ->group(function () {
            Route::get('/', [ChecklistController::class, 'index']);
            Route::get('/{checklist}', [ChecklistController::class, 'show']);
            Route::get('/{checklist}/staff', [ChecklistController::class, 'staff']);
            Route::post('/{checklist}/submit', [ChecklistController::class, 'submit']);
        });

    // Checklist Reminders
    Route::controller(\App\Http\Controllers\Api\v1\ChecklistReminderController::class)
        ->prefix('/checklist-reminders')
        ->group(function () {
            Route::get('/pending-count', 'getPendingCount');
            Route::get('/pending-by-date', 'getPendingByDate');
            Route::post('/mark-as-read', 'markAsRead');
        });

    // Reimbursements
    Route::get('/reimbursements', [\App\Http\Controllers\Api\v1\ReimbursementController::class, 'index']);
    Route::post('/reimbursements', [\App\Http\Controllers\Api\v1\ReimbursementController::class, 'store']);
    Route::get('/reimbursements/{reimbursement}', [\App\Http\Controllers\Api\v1\ReimbursementController::class, 'show']);
    Route::put('/reimbursements/{reimbursement}', [\App\Http\Controllers\Api\v1\ReimbursementController::class, 'update']);
    Route::post('/reimbursements/{reimbursement}/cancel', [\App\Http\Controllers\Api\v1\ReimbursementController::class, 'cancel']);


    Route::controller(ChecklistCategoryController::class)
        ->prefix('/checklist-categories')
        ->group(function () {
            Route::get('/', [ChecklistCategoryController::class, 'index']);
            Route::get('/{id}', [ChecklistCategoryController::class, 'show']);
        });

    Route::controller(DepartmentController::class)
        ->prefix('/departments')
        ->group(function () {
            Route::get('/', [DepartmentController::class, 'index']);
            Route::get('/{id}', [DepartmentController::class, 'show']);
            Route::get('/{id}/employees', [DepartmentController::class, 'getEmployees']);
        });

    Route::controller(VehicleController::class)
        ->prefix('/vehicles')
        ->group(function () {
            Route::get('/', [VehicleController::class, 'index']);
            Route::get('/{id}', [VehicleController::class, 'show']);
        });

    Route::controller(EmployeeController::class)
        ->prefix('/employees')
        ->group(function () {
            Route::get('/', [EmployeeController::class, 'index']);
            Route::get('/{id}', [EmployeeController::class, 'show']);
        });

    Route::controller(AttendanceController::class)
        ->prefix('/attendances')
        ->group(function () {
            Route::get('/', [AttendanceController::class, 'index']);
            Route::get('/history', [AttendanceController::class, 'history']);
            Route::get('/log', [AttendanceController::class, 'logAbsensi']);
            Route::post('/hit', [AttendanceController::class, 'hit']);
            Route::get('/{id}', [AttendanceController::class, 'show']);
        });

    Route::controller(FinancialInformationController::class)
        ->prefix('/financialinformations')
        ->group(function () {
            Route::get('/', 'index');
            Route::get('/{id}', 'show');
        });

    Route::controller(ReportController::class)
        ->prefix('/reports')
        ->group(function () {
            Route::get('/', 'index');
            Route::get('/{id}', 'show');
            Route::post('/', 'store');
        });

    // Report Comments API
    Route::controller(\App\Http\Controllers\Api\v1\ReportCommentController::class)
        ->prefix('/reports/{reportId}/comments')
        ->group(function () {
            Route::get('/', 'index');           // GET /api/v1/reports/{reportId}/comments
            Route::post('/', 'store');          // POST /api/v1/reports/{reportId}/comments
            Route::get('/{commentId}', 'show'); // GET /api/v1/reports/{reportId}/comments/{commentId}
            Route::put('/{commentId}', 'update');   // PUT /api/v1/reports/{reportId}/comments/{commentId}
            Route::delete('/{commentId}', 'destroy'); // DELETE /api/v1/reports/{reportId}/comments/{commentId}
        });

    // Overtime history for authenticated employee
    Route::get('/overtimes/history', [\App\Http\Controllers\Api\v1\EmployeeOvertimeController::class, 'history']);

    // Receivable history for authenticated employee
    Route::get('/receivables/history', [\App\Http\Controllers\Api\v1\ReceivableHistoryController::class, 'history']);

    // Receivable payments (using web auth for session-based authentication)
    Route::middleware(['web', 'auth:web'])->group(function () {
        // Checklist respondents management
        Route::get('/checklists/{checklist}/employees', [\App\Http\Controllers\Api\v1\ChecklistRespondentController::class, 'getEmployees']);
        Route::get('/checklists/{checklist}/respondents', [\App\Http\Controllers\Api\v1\ChecklistRespondentController::class, 'index']);
        Route::post('/checklists/{checklist}/respondents', [\App\Http\Controllers\Api\v1\ChecklistRespondentController::class, 'store']);
        Route::delete('/checklists/{checklist}/respondents/{employeeId}', [\App\Http\Controllers\Api\v1\ChecklistRespondentController::class, 'destroy']);
        Route::get('/receivables/{receivableId}/payments', [\App\Http\Controllers\Api\v1\ReceivablePaymentController::class, 'index']);
        Route::post('/receivables/{receivableId}/payments', [\App\Http\Controllers\Api\v1\ReceivablePaymentController::class, 'store']);
        Route::delete('/receivables/payments/{paymentId}', [\App\Http\Controllers\Api\v1\ReceivablePaymentController::class, 'destroy']);
    });

    // Financial summary (profile base salary vs latest slip metadata)
    Route::get('/financial/summary', [\App\Http\Controllers\Api\v1\FinancialSummaryController::class, 'summary']);

    // Announcement
    Route::controller(ApiAnnouncementController::class)
        ->prefix('/announcements')
        ->group(function () {
            Route::get('/', 'index');
            Route::get('/{id}', 'show');
        });

    // Work principles
    Route::controller(WorkPrincipleController::class)
        ->prefix('/work-principles')
        ->group(function () {
            Route::get('/prinsip', 'prinsip');
            Route::get('/etos-kerja', 'etosKerja');
        });

    // Stock Recap API
    Route::post('/stocks/recap', [ApiStockRecapController::class, 'index']);
    Route::post('/stocks/update', [ApiStockRecapController::class, 'update']);

    // Vehicle odometer history
    Route::get('/vehicle-odometers', [VehicleOdometerHistoryController::class, 'index']);
    Route::post('/vehicle-odometers', [VehicleOdometerHistoryController::class, 'update']);

    // Calendar
    Route::get('/calendar', [CalenderController::class, 'index']);

    // Notifications
    Route::controller(NotificationController::class)
        ->prefix('/notifications')
        ->group(function () {
            Route::get('/history', 'history');
            Route::post('/', 'store');
            Route::put('/{id}/read', 'read');
        });

    // Announcements (API)
    Route::controller(ApiAnnouncementController::class)
        ->prefix('/announcements')
        ->group(function () {
            Route::get('/', 'index');
            Route::get('/{announcement}', 'show');
        });

    // Compliances (API v1)
    Route::controller(ApiComplianceController::class)
        ->prefix('/compliances')
        ->group(function () {
            Route::get('/', 'index');
            Route::get('/{compliance}', 'show');
            Route::post('/', 'store');
            Route::put('/{compliance}', 'update');
            Route::patch('/{compliance}', 'update');
            Route::delete('/{compliance}', 'destroy');
        });

    Route::controller(UploadController::class)
        ->prefix('/upload')
        ->group(function () {
            Route::post('/single', 'single');
            Route::post('/multiple', 'multiple');
            Route::post('/delete/{type}/{id}', 'destroy');
        });



    // Simple test endpoint to push to current authenticated user device
    // Route::post('/push/test', PushNotificationController::class);

    Route::controller(InspectionController::class)
        ->prefix('/inspections')
        ->group(function () {
            Route::get('/history', 'history');
            Route::post('/', 'store');
            Route::get('/{id}', 'detail');
            Route::get('/{id}/answers', 'answers');
            Route::post('/group', 'storeGroup');
            Route::get('{inspectionId}/checklist/draft/{id}', 'showDraftByChecklistId');
        });

    Route::controller(BranchController::class)
        ->prefix('/branchs')
        ->group(function () {
            Route::get('/', [BranchController::class, 'index']);
            Route::get('/{id}', [BranchController::class, 'show']);
        });

    // --- Items ---
    Route::controller(ItemController::class)
        ->prefix('/items')
        ->group(function () {
            Route::get('/', 'index');
            Route::get('/{id}', 'show');
        });

    // Item stock history
    Route::get('/item-stocks/history', [\App\Http\Controllers\Api\v1\ItemStockHistoryController::class, 'index']);

    // Submissions list (SPV can see all)
    Route::get('/submissions', [\App\Http\Controllers\Api\v1\SubmissionController::class, 'index']);

    Route::prefix('submission')->group(function () {
        Route::prefix('leaves')->group(function () {
            // leave types
            Route::apiResource('types', LeaveTypeController::class)->only(['index', 'show']);

            // leave requests
            Route::apiResource('requests', EmployeeLeaveRequestController::class)->only(['index', 'show', 'store', 'update']);

            // Calculate leave days based on scheduled shifts within date range
            Route::post('requests/calculate-days', [EmployeeLeaveRequestController::class, 'calculateDays']);

            // Cancel leave request
            Route::post('requests/{id}/cancel', [EmployeeLeaveRequestController::class, 'cancel']);
            // Reject leave request
            Route::post('requests/{id}/reject', [EmployeeLeaveRequestController::class, 'reject']);
            // Approve leave request
            Route::post('requests/{id}/approve', [EmployeeLeaveRequestController::class, 'approve']);

            // // Get approvals by request
            // Route::apiResource('approvals', EmployeeLeaveApproval::class)->only(['index']);

            // // Approve a leave (per approval ID)
            // Route::post('approvals/{id}/approve', [EmployeeLeaveApprovalController::class, 'approve']);

            // // Reject a leave (per approval ID)
            // Route::post('approvals/{id}/reject', [EmployeeLeaveApprovalController::class, 'reject']);

            // leave balance (new endpoint)
            Route::get('balance', [EmployeeLeaveBalanceController::class, 'getLeaveBalance']);
        });

        Route::prefix('usage')->group(function () {
            Route::get('/{usageId}', [ApiSubmissionUsageController::class, 'show']);
            Route::post('/', [ApiSubmissionUsageController::class, 'create']);
            Route::put('/{usageId}', [ApiSubmissionUsageController::class, 'update']);
            Route::post('/{usageId}/approve', [ApiSubmissionUsageController::class, 'approve']);
            Route::post('/{usageId}/approve-mobile', [ApiSubmissionUsageController::class, 'approve']);
            Route::post('/{usageId}/reject-mobile', [ApiSubmissionUsageController::class, 'rejectMobile']);
            Route::post('/{usageId}/reject', [ApiSubmissionUsageController::class, 'reject']);
        });

        Route::prefix('loan')->group(function () {
            Route::get('/{kodeLoan}', [SubmissionLoanController::class, 'show']);
            Route::post('/', [SubmissionLoanController::class, 'create']);
            Route::put('/{kodeLoan}', [SubmissionLoanController::class, 'update']);
            Route::post('/{kodeLoan}/approve', [SubmissionLoanController::class, 'approve']);
            Route::post('/{kodeLoan}/cancel', [SubmissionLoanController::class, 'cancel']);
            Route::post('/{kodeLoan}/reject', [SubmissionLoanController::class, 'reject']);
        });

        Route::prefix('procurement')->group(function () {
            Route::get('/{materialId}', [ApiSubmissionProcurementController::class, 'show']);
            Route::post('/', [ApiSubmissionProcurementController::class, 'create']);
            Route::put('/{materialId}', [ApiSubmissionProcurementController::class, 'update']);
            Route::post('/{materialId}/approve', [ApiSubmissionProcurementController::class, 'approve']);
            Route::post('/{materialId}/reject', [ApiSubmissionProcurementController::class, 'reject']);
        });

        Route::prefix('general')->group(function () {
            Route::get('/', [ApiSubmissionGeneralController::class, 'index'])
                ->name('submission.general.index.mobile');
            Route::get('/search', [ApiSubmissionGeneralController::class, 'search'])
                ->name('submission.general.search.mobile');
            Route::get('/{generalId}', [ApiSubmissionGeneralController::class, 'show'])
                ->name('submission.general.show.mobile');
            Route::post('/', [ApiSubmissionGeneralController::class, 'store']);
            Route::put('/{generalId}', [ApiSubmissionGeneralController::class, 'update']);
            Route::put('/{generalId}/update-status', [ApiSubmissionGeneralController::class, 'updateStatus']);
            Route::delete('/{generalId}/destroy', [ApiSubmissionGeneralController::class, 'destroy']);
        });


        Route::apiResource('overtimes', EmployeeOvertimeController::class)->only(['index', 'show', 'store', 'update', 'destroy']);
        Route::post('overtimes/{id}/status', [EmployeeOvertimeController::class, 'changeStatus']);
        Route::post('overtimes/{id}/cancel', [EmployeeOvertimeController::class, 'cancel']);
    });

    Route::prefix('presences')->group(function () {
        Route::post('/', [PresenceController::class, 'store']);
    });

    Route::prefix('performances')->group(function () {
        Route::get('/', [EmployeePerformanceController::class, 'index']);
        Route::get('/detail', [EmployeePerformanceController::class, 'show']);
        Route::post('/', [EmployeePerformanceController::class, 'store']);
        Route::delete('/', [EmployeePerformanceController::class, 'destroy']);
        Route::post('/sync', [EmployeePerformanceController::class, 'sync']);
    });
});

// Dashboard API (no auth required for now)
Route::prefix('dashboard')->group(function () {
    Route::get('/employee-proportion-by-branch', [\App\Http\Controllers\DashboardController::class, 'getEmployeeProportionByBranch']);
    Route::get('/top-performers-by-branch', [\App\Http\Controllers\DashboardController::class, 'getTopPerformersByBranch']);
});
