<?php

namespace App\Http\Controllers;

use App\Actions\Data\Dashboard\GetAbsensiChartData;
use App\Actions\Data\Dashboard\GetBarangChartData;
use App\Actions\Data\Dashboard\GetBarangMetric;
use App\Actions\Data\Dashboard\GetMaterialRequestData;
use App\Actions\Data\Dashboard\GetGoodReceiptData;
use App\Actions\Data\Dashboard\GetLowStockItems;
use App\Actions\Data\Dashboard\GetMostFrequentlyUsedItems;
use App\Actions\Data\Dashboard\GetItemProportions;
use App\Actions\Data\Dashboard\GetCalendarEvents;
use App\Actions\Data\Dashboard\GetRecentActivities;
use App\Actions\Data\Dashboard\GetTodayActivities;
use App\Actions\Data\Dashboard\GetKaryawanMetrics;
use App\Actions\Data\Dashboard\GetKaryawanTodayActivities;
use App\Actions\Data\Dashboard\GetExemplaryEmployees;
use App\Actions\Data\Dashboard\GetUpcomingBirthdays;
use App\Actions\Data\Dashboard\GetEmployeeProportionData;
use App\Actions\Data\Dashboard\GetEmployeeProportionByBranch;
use App\Actions\Data\Dashboard\GetGenderProportionData;

use App\Actions\Data\Dashboard\GetEmployeeGrowthData;
use App\Actions\Data\Dashboard\GetTopPerformers;
use App\Models\Branch;
use App\Models\Department;
use App\Models\Employee;
use App\Models\Item;
use App\Models\ItemStock;
use Inertia\Inertia;
use Carbon\Carbon;
use Illuminate\Support\Facades\Gate;

class DashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        // If user doesn't have dashboard access, redirect to first accessible page
        if (!$user->can('dashboard.view')) {
            return $this->redirectToFirstAccessiblePage($user);
        }

        // Get current date for filtering
        $today = Carbon::today();
        $currentMonth = Carbon::now()->month;
        $currentYear = Carbon::now()->year;

        // Helper function to safely execute actions
        $safeExecute = function ($action, $fallback = null, ...$args) {
            try {
                return app($action)->execute(...$args);
            } catch (\Exception $e) {
                logger()->warning("Dashboard action failed: " . $action, [
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
                return $fallback;
            }
        };

        // Dashboard metrics
        // Get total stock from latest ItemStock records using subquery
        $totalItemsStock = ItemStock::whereIn('id', function ($query) {
            $query->selectRaw('MAX(id)')
                ->from('item_stocks')
                ->groupBy('item_id');
        })->sum('last_stock') ?? 0;

        // Get low stock items count (items with latest stock < 40)
        $lowStockItemsCount = Item::whereHas('stock', function ($query) {
            $query->where('last_stock', '<', 40);
        })->count();

        $metrics = [
            'active_employees' => Employee::count(),
            'total_items_stock' => (int) $totalItemsStock,
            'low_stock_items' => $lowStockItemsCount,
            'today_activities' => $safeExecute(GetTodayActivities::class, 0, $today),
        ];

        // Chart data for Barang (Items)
        // $barangData = $safeExecute(GetBarangChartData::class, ['chartData' => [
        //     'categories' => ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'],
        //     'series' => [
        //         'masuk' => array_fill(0, 12, 0),
        //         'keluar' => array_fill(0, 12, 0)
        //     ]
        // ]], $currentYear);

        $barangData = app(GetBarangChartData::class)->execute($currentYear);

        $barangChartData = $barangData['chartData'] ?? [
            'categories' => ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'],
            'series' => [
                'masuk' => array_fill(0, 12, 0),
                'keluar' => array_fill(0, 12, 0)
            ]
        ];

        // Barang metrics
        $barangMetric = app(GetBarangMetric::class)->execute();

        // Material Request data
        $requestData = $safeExecute(GetMaterialRequestData::class, [
            'current' => 0,
            'total_2026' => 0,
            'completed' => 0,
            'total_2024' => 0
        ]);

        // Good Receipt data
        $receiptData = $safeExecute(GetGoodReceiptData::class, [
            'current' => 0,
            'total_2026' => 0,
            'completed' => 0,
            'total_2024' => 0
        ]);

        // All items
        $lowStockItems = $safeExecute(GetLowStockItems::class, [], 50);

        // Most frequently used items
        $mostFrequentlyUsedItems = $safeExecute(GetMostFrequentlyUsedItems::class, [], 6);

        // Item proportions by category
        $itemProportions = app(GetItemProportions::class)->execute();


        // Chart data for Absensi (Attendance)
        $absensiData = $safeExecute(GetAbsensiChartData::class, [
            'categories' => ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'],
            'series' => [
                'total_masuk' => array_fill(0, 12, 0)
            ]
        ], $currentYear);

        // Recent activities
        $recentActivities = $safeExecute(GetRecentActivities::class, []);

        // Calendar events
        $calendarEvents = $safeExecute(GetCalendarEvents::class, []);

        // Karyawan data
        $karyawanMetrics = $safeExecute(GetKaryawanMetrics::class, [
            'total_karyawan' => 0,
            'active_karyawan' => 0,
            'late_karyawan' => 0,
            'on_leave' => 0,
            'performance' => '0%'
        ]);

        $karyawanTodayActivities = $safeExecute(GetKaryawanTodayActivities::class, []);
        $exemplaryEmployees = $safeExecute(GetExemplaryEmployees::class, []);
        $topPerformers = $safeExecute(GetTopPerformers::class, []);
        $upcomingBirthdays = $safeExecute(GetUpcomingBirthdays::class, []);
        $employeeProportionData = $safeExecute(GetEmployeeProportionData::class, [
            'categories' => ['Balikpapan', 'Samarinda', 'Kantor Pusat', 'Bontang', 'IKN'],
            'data' => [0, 0, 0, 0, 0]
        ]);
        $genderProportionData = $safeExecute(GetGenderProportionData::class, [0, 0]);
        $employeeGrowthData = $safeExecute(GetEmployeeGrowthData::class, [
            'categories' => ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'],
            'data' => array_fill(0, 12, 0)
        ], $currentYear);

        $branches = Branch::select('id', 'name')->get();
        $departments = Department::select('id', 'name', 'branch_id')->get();

        return Inertia::render('Admin/Dashboard', [
            'metrics' => $metrics,
            'barangData' => $barangChartData,
            'barangMetric' => $barangMetric,
            'requestData' => $requestData,
            'receiptData' => $receiptData,
            'lowStockItems' => $lowStockItems,
            'mostFrequentlyUsedItems' => $mostFrequentlyUsedItems,
            'itemProportions' => $itemProportions,
            'absensiData' => $absensiData,
            'recentActivities' => $recentActivities,
            'calendarEvents' => $calendarEvents,
            'karyawanMetrics' => $karyawanMetrics,
            'karyawanTodayActivities' => $karyawanTodayActivities,
            'exemplaryEmployees' => $exemplaryEmployees,
            'topPerformers' => $topPerformers,
            'upcomingBirthdays' => $upcomingBirthdays,
            'employeeProportionData' => $employeeProportionData,
            'genderProportionData' => $genderProportionData,
            'employeeGrowthData' => $employeeGrowthData,
            'branches' => $branches,
            'departments' => $departments,
        ]);
    }

    public function getTopPerformersByBranch()
    {
        try {
            $branchId = request('branch_id');
            // If branchId is "null" string or empty, treat as null
            if ($branchId === 'null' || $branchId === '') {
                $branchId = null;
            }

            $departmentId = request('department_id');
            if ($departmentId === 'null' || $departmentId === '') {
                $departmentId = null;
            }

            $topPerformers = app(GetTopPerformers::class)->execute(3, $branchId, $departmentId);

            return response()->json([
                'success' => true,
                'data' => $topPerformers
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error fetching top performers data',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function getEmployeeProportionByBranch()
    {
        try {
            $branchId = request('branch_id');
            $employeeProportionData = app(GetEmployeeProportionByBranch::class)->execute($branchId);

            return response()->json([
                'success' => true,
                'data' => $employeeProportionData
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error fetching employee proportion data',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Redirect user to first accessible page based on their permissions
     */
    private function redirectToFirstAccessiblePage($user)
    {
        // Define routes in priority order
        $routePermissions = [
            // Inspections
            ['route' => 'inspections.index', 'permission' => 'inspections.view', 'name' => 'Hasil Inspeksi'],

            // Reports
            ['route' => 'reports.index', 'permission' => 'reports.view', 'name' => 'Laporan'],

            // Submissions
            ['route' => 'submissions.index', 'permission' => 'submission_all.view', 'name' => 'Pengajuan'],

            // Attendance
            ['route' => 'presences.index', 'permission' => 'presences.view', 'name' => 'Absensi'],

            // Employees
            ['route' => 'employees.index', 'permission' => 'employees.view', 'name' => 'Daftar Karyawan'],

            // Vehicles
            ['route' => 'vehicles.index', 'permission' => 'vehicles.view', 'name' => 'Data Kendaraan'],

            // Items
            ['route' => 'items.index', 'permission' => 'items.view', 'name' => 'Daftar Barang'],

            // Checklists
            ['route' => 'checklists.index', 'permission' => 'checklists.view', 'name' => 'Daftar Checklist'],

            // Announcements
            ['route' => 'announcements.index', 'permission' => 'announcements.view', 'name' => 'Pengumuman'],

            // Profile (fallback - everyone should have access)
            ['route' => 'profile.show', 'permission' => null, 'name' => 'Profil'],
        ];

        foreach ($routePermissions as $item) {
            // If no permission required or user has permission
            if ($item['permission'] === null || $user->can($item['permission'])) {
                // Check if route exists
                if (\Route::has($item['route'])) {
                    return redirect()
                        ->route($item['route'])
                        ->with('info', "Anda tidak memiliki akses ke dashboard. Dialihkan ke halaman {$item['name']}.");
                }
            }
        }

        // If no accessible route found, logout user and redirect to login with error
        \Auth::logout();

        // Invalidate session
        request()->session()->invalidate();
        request()->session()->regenerateToken();

        return redirect()
            ->route('login')
            ->with('error', 'Akun Anda tidak memiliki akses ke sistem ini. Silakan hubungi administrator untuk mendapatkan hak akses yang sesuai.');
    }
}
