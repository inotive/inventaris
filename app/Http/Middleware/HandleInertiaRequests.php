<?php

namespace App\Http\Middleware;

use Inertia\Middleware;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\Submission;
use App\Enums\SubmissionStatusEnum;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that's loaded on the first page visit.
     *
     * @see https://inertiajs.com/server-side-setup#root-template
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determines the current asset version.
     *
     * @see https://inertiajs.com/asset-versioning
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @see https://inertiajs.com/shared-data
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {

        return array_merge(parent::share($request), [
            'auth' => [
                'user' => Auth::check() ? Auth::user()->load('employee') : null,
                'roles' => Auth::check() ? Auth::user()->getRoleNames() : [],
                'permissions' => Auth::check() ? Auth::user()->getAllPermissions()->pluck('name') : [],
            ],
            'flash' => [
                'success' => fn() => $request->session()->get('success'),
                'error' => fn() => $request->session()->get('error'),
                'duplicates' => fn() => $request->session()->get('duplicates'),
            ],
            'errors' => fn() => $request->session()->get('errors')
                ? $request->session()->get('errors')->toArray()
                : (object) [],
            'pendingSubmissionsCount' => fn() => Auth::check()
                ? cache()->remember('pending_submissions_count_' . Auth::id(), 60, function () {
                    $user = Auth::user();
                    $isSuperAdmin = method_exists($user, 'hasRole') && $user->hasRole('Superadmin');
                    $branchId = optional($user->employee)->branch_id;

                    // Base query
                    $query = DB::table('v_pending_submissions_count')
                        ->where('source_table', 'submissions');

                    // Filter by branch if not Superadmin and has branch_id
                    if (!$isSuperAdmin && $branchId && $branchId != 2) {
                        $query->where('branch_id', $branchId);
                    }

                    $viewData = $query->get();

                    $byType = [];
                    $total = 0;

                    foreach ($viewData as $row) {
                        // Aggregate counts (in case multiple rows per type exist, though grouping by branch should prevent it if filtered)
                        if (!isset($byType[$row->submission_type])) {
                            $byType[$row->submission_type] = 0;
                        }
                        $byType[$row->submission_type] += $row->pending_count;
                        $total += $row->pending_count;
                    }

                    return [
                        'total' => $total,
                        'by_type' => $byType,
                    ];
                })
                : ['total' => 0, 'by_type' => []],
            'pendingUsersCount' => fn() => Auth::check() && Auth::user()->can('users.view')
                ? \App\Models\User::where('status', 'pending')->count()
                : 0,
            'goodsTransactionsPendingCount' => fn() => Auth::check()
                ? cache()->remember('goods_transactions_pending_count_' . Auth::id(), 60, function () {
                    $user = Auth::user();
                    $isSuperAdmin = method_exists($user, 'hasRole') && $user->hasRole('Superadmin');
                    $branchId = optional($user->employee)->branch_id;

                    // 1. material_requests: pending status is 'pending'
                    $mrQuery = DB::table('material_requests')
                        ->where('status', 'pending');
                    if (!$isSuperAdmin && !$user->can('material_requests.view_global') && $branchId) {
                        $mrQuery->join('departments', 'material_requests.department_id', '=', 'departments.id')
                            ->where('departments.branch_id', $branchId);
                    }
                    $mrCount = $mrQuery->count();

                    // 2. purchase_requests: pending status is 'on_progress'
                    $prQuery = DB::table('purchase_requests')
                        ->where('status', 'on_progress');
                    if (!$isSuperAdmin && !$user->can('purchase_requests.view_global') && $branchId) {
                        $prQuery->join('departments', 'purchase_requests.department_id', '=', 'departments.id')
                            ->where('departments.branch_id', $branchId);
                    }
                    $prCount = $prQuery->count();

                    // 3. purchase_orders: pending status is status_delivered = 'pending'
                    $poQuery = DB::table('purchase_orders')
                        ->where('status_delivered', 'pending');
                    if (!$isSuperAdmin && !$user->can('purchase_orders.view_global') && $branchId) {
                        $poQuery->where('branch_id', $branchId);
                    }
                    $poCount = $poQuery->count();

                    // 4. good_issues: pending status is 'pending'
                    $giQuery = DB::table('good_issues')
                        ->where('status', 'pending');
                    if (!$isSuperAdmin && !$user->can('good_issues.view_global') && $branchId) {
                        $giQuery->join('departments', 'good_issues.department_id', '=', 'departments.id')
                            ->where('departments.branch_id', $branchId);
                    }
                    $giCount = $giQuery->count();

                    // 5. good_transfers: pending status is 'Shipped'
                    $gtQuery = DB::table('good_transfers')
                        ->where('status', 'Shipped');
                    if (!$isSuperAdmin && !$user->can('good_transfers.view_global') && $branchId) {
                        $gtQuery->where(function($q) use ($branchId) {
                            $q->where('from_branch', $branchId)->orWhere('to_branch', $branchId);
                        });
                    }
                    $gtCount = $gtQuery->count();

                    $byType = [
                        'material_requests' => $mrCount,
                        'purchase_requests' => $prCount,
                        'purchase_orders' => $poCount,
                        'good_issues' => $giCount,
                        'good_transfers' => $gtCount,
                    ];

                    return [
                        'total' => array_sum($byType),
                        'by_type' => $byType,
                    ];
                })
                : ['total' => 0, 'by_type' => []],
            // Count all unresolved face mismatch attendances (not limited to today)
            'faceMismatchCount' => fn() => Auth::check() && Auth::user()->hasAnyRole(['Admin', 'Superadmin', 'SPV', 'HRD'])
                ? cache()->remember('face_mismatch_count_' . Auth::id(), 60, function () {
                    $user = Auth::user();
                    $isSuperadmin = method_exists($user, 'hasRole') && $user->hasRole('Superadmin');
                    $branchId = optional($user->employee)->branch_id;

                    $query = \App\Models\Attendance::query()
                        ->where('is_face_correct', '>=', 1);

                    if (!$isSuperadmin && $branchId && $branchId != 2) {
                        $query->whereHas('employee', function ($q) use ($branchId) {
                            $q->where('branch_id', $branchId);
                        });
                    }

                    return $query->count();
                })
                : 0,
        ]);
    }
}
