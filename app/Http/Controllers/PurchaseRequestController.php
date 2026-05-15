<?php

namespace App\Http\Controllers;

use App\Actions\Data\PurchaseRequest\ApprovePurchasedRequest;
use App\Actions\Data\PurchaseRequest\CreatePurchaseLogActivities;
use App\Actions\Data\PurchaseRequest\CreatePurchaseRequest;
use App\Models\Branch;
use Exception;
use Throwable;
use App\Models\Department;
use App\Models\Employee;
use App\Models\Item;
use App\Models\MaterialRequest;
use App\Models\PurchaseRequest;
use App\Models\PurchaseRequestActivity;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;

class PurchaseRequestController extends Controller
{
    public function index0(Request $request)
    {
        // Placeholder data for layout; replace with real query later
        $statuses = ['draft', 'waiting_approval', 'approved', 'rejected', 'linked_to_po'];
        $filters = [
            'q' => $request->string('q')->toString(),
            'status' => $request->input('status'),
            'date_from' => $request->input('date_from'),
            'date_to' => $request->input('date_to'),
        ];

        $purchaseRequests = [
            // Demo rows to render UI
            [
                'id' => 1,
                'number' => 'PR-2026-0001',
                'date' => now()->toDateString(),
                'requester' => ['id' => 1, 'name' => 'John Doe'],
                'department' => 'Finance',
                'from_mr' => ['id' => 9, 'number' => 'MR-2026-0009'],
                'items_count' => 3,
                'status' => 'draft',
            ],
        ];

        return Inertia::render('Admin/PurchaseRequests/Index', [
            'purchaseRequests' => [
                'data' => $purchaseRequests,
                'current_page' => 1,
                'per_page' => 15,
            ],
            'filters' => $filters,
            'statusOptions' => $statuses,
            'departments' => Department::select('id', 'name')->orderBy('name')->get(),
            'employees' => Employee::select('id', 'name')->orderBy('name')->limit(200)->get(),
        ]);
    }

    // Create Purchase Request from an existing Material Request
    public function fromMaterialRequest(MaterialRequest $materialRequest)
    {
        // Prepare default values based on Material Request
        $requestNo = PurchaseRequest::generateRequestNo();

        DB::transaction(function () use ($materialRequest, $requestNo) {
            $purchaseRequest = PurchaseRequest::create([
                'request_id' => $materialRequest->id,
                'request_no' => $requestNo,
                'requested_at' => now()->toDateString(),
                'requested_by' => $materialRequest->requested_by,
                'department_id' => $materialRequest->department_id,
                'requirement' => $materialRequest->requirement,
            ]);

            // Copy items
            foreach ($materialRequest->items as $it) {
                $purchaseRequest->items()->create([
                    'item_id' => $it->item_id,
                    'quantity_requested' => $it->quantity_requested,
                    'note' => $it->note,
                ]);
            }

            // activity log
            app(CreatePurchaseLogActivities::class)->execute($purchaseRequest->id, 'Membuat Permintaan dari Permintaan Barang', 'Permintaan baru dibuat dengan nomor ' . $purchaseRequest->request_no . ' dari Permintaan Barang ' . $materialRequest->request_no);
        });


        return redirect()->route('purchase-requests.index')
            ->with('success', 'Pengajuan pembelian berhasil dibuat dari Permintaan Barang ' . $materialRequest->request_no);
    }

    public function index(Request $request)
    {
        $filters = [
            'q' => $request->string('q')->toString(),
            'status' => $request->has('status') ? $request->string('status')->toString() : null,
            'department_id' => $request->integer('department_id') ?: null,
            'employee_id' => $request->integer('employee_id') ?: null,
            'date_from' => $request->string('date_from')->toString(),
            'date_to' => $request->string('date_to')->toString(),
        ];

        $sortBy = $request->get('sortBy', 'created_at');
        $sortDirection = $request->get('sortDirection', 'desc');
        $groupBy = $request->get('groupBy'); // department, requested_by, status, null


        $isSuperadmin = Auth::user()->hasRole('Superadmin');
        $userBranchId = Auth::user()->employee->branch_id ?? null;
        $departmentIds = Department::where('branch_id', $userBranchId)->pluck('id');

        $query = PurchaseRequest::query()
            ->with(['request:id,request_no', 'department:id,name', 'requester:id,name', 'approver:id,name'])
            ->when(!$isSuperadmin && $userBranchId != 2, function ($query) use ($departmentIds) {
                return $query->whereIn('department_id', $departmentIds);
            });

        // Apply filters
        if ($filters['q']) {
            $query->where(function ($q) use ($filters) {
                $search = $filters['q'];
                $q->where('request_no', 'like', "%{$search}%")
                    ->orWhereHas('request', fn($qr) => $qr->where('request_no', 'like', "%{$search}%"))
                    ->orWhereHas('requester', fn($qr) => $qr->where('name', 'like', "%{$search}%"))
                    ->orWhereHas('department', fn($qd) => $qd->where('name', 'like', "%{$search}%"))
                    ->orWhere('status', 'like', "%{$search}%")
                    // ->orWhereRaw("DATE_FORMAT(requested_at, '%Y-%m-%d') like ?", ["%{$search}%"])
                ;
            });
        }

        // Status filtering
        if ($filters['status']) {
            $query->where('status', $filters['status']);
        }

        // Other filters
        if ($filters['department_id']) $query->where('department_id', $filters['department_id']);
        if ($filters['employee_id']) $query->where('requested_by', $filters['employee_id']);
        if ($filters['date_from']) $query->whereDate('requested_at', '>=', $filters['date_from']);
        if ($filters['date_to']) $query->whereDate('requested_at', '<=', $filters['date_to']);

        // Sorting and grouping
        $sortDirection = strtolower($sortDirection) === 'asc' ? 'asc' : 'desc';

        // Priority for on_progress when showing all statuses
        // if ($request->has('status') && !$filters['status']) {
        //     $query->orderByRaw("CASE WHEN status = 'on_progress' THEN 0 ELSE 1 END");
        // }

        // Group by ordering (use subquery orderBy for related names to avoid JOINs)
        if ($groupBy === 'department') {
            $query->orderBy(
                Department::select('name')
                    ->whereColumn('departments.id', 'purchase_requests.department_id')
            );
        }

        if ($groupBy === 'requested_by') {
            $query->orderBy(
                Employee::select('name')
                    ->whereColumn('employees.id', 'purchase_requests.requested_by')
            );
        }

        if ($groupBy === 'status') {
            $query->orderBy('status');
        }

        // Column sorting
        $validSortColumns = ['request_no', 'requested_at', 'status'];
        if (in_array($sortBy, $validSortColumns)) {
            $query->orderBy($sortBy, $sortDirection);
        } elseif ($sortBy === 'department') {
            $query->orderBy(
                Department::select('name')
                    ->whereColumn('departments.id', 'purchase_requests.department_id'),
                $sortDirection
            );
        } elseif ($sortBy === 'requested_by') {
            $query->orderBy(
                Employee::select('name')
                    ->whereColumn('employees.id', 'purchase_requests.requested_by'),
                $sortDirection
            );
        } else {
            $query->orderBy('created_at', 'desc');
        }

        $perPage = $request->integer('per_page', 10);
        $purchaseRequests = $query->orderBy('created_at', 'desc')->paginate($perPage)->withQueryString();


        $canEdit = Auth::user()->can('purchase_requests.edit');
        $canDelete = Auth::user()->can('purchase_requests.delete');
        $canView = Auth::user()->can('purchase_requests.view');
        $canCreate = Auth::user()->can('purchase_requests.create');


        $departments = Department::query()->when(!$isSuperadmin && $userBranchId != 2, function ($query) use ($departmentIds) {
            return $query->whereIn('id', $departmentIds);
        })->select('id', 'name')->orderBy('name')->get();
        $employees = Employee::query()->when(!$isSuperadmin && $userBranchId != 2, function ($query) use ($departmentIds) {
            return $query->whereIn('department_id', $departmentIds);
        })->select('id', 'name')->orderBy('name')->get();

        // Reflect the effective status back to the UI
        $effectiveStatus = $request->has('status') ? ($filters['status'] ?? '') : null;

        return Inertia::render('Admin/PurchaseRequests/Index', [
            'purchaseRequests' => $purchaseRequests->through(function ($mr) {
                return [
                    'id' => $mr->id,
                    'request_no' => $mr->request_no,
                    'mr_no' => $mr->request?->request_no,
                    'department' => $mr->department?->name,
                    'requested_by' => $mr->requester?->name,
                    'requested_at' => $mr->requested_at->locale('id')->translatedFormat('d F Y'),
                    'status' => $mr->status,
                ];
            }),
            'departments' => $departments,
            'employees' => $employees,
            'filters' => array_merge($filters, ['status' => $effectiveStatus]),
            'sortBy' => $sortBy,
            'sortDirection' => $sortDirection,
            'groupBy' => in_array($groupBy, ['department', 'requested_by', 'status'], true) ? $groupBy : null,
            // Sidebar counters
            'sidebarCounts' => [
                'total' => PurchaseRequest::count(),
                'on_progress' => PurchaseRequest::where('status', 'on_progress')->count(),
            ],
            'can_edit' => $canEdit,
            'can_delete' => $canDelete,
            'can_view' => $canView,
            'can_create' => $canCreate,
        ]);
    }

    public function create()
    {
        $requestNo = PurchaseRequest::generateRequestNo();
        $departments = Department::with(['branch'])->orderBy('name')->get();
        $employees = Employee::orderBy('name')->get();

        $user = Auth::user()->load(['employee', 'employee.department']);
        $isSuperadmin = $user->hasRole('Superadmin');
        $userBranchId = $user->employee->branch_id ?? null;
        $branches = Branch::where('id', $userBranchId)->pluck('id');
        $departmentIds = Department::where('branch_id', $userBranchId)->pluck('id');

        $items = Item::with(['unit', 'stock:id,item_id,last_stock'])->orderBy('name')->when(!$isSuperadmin && empty($filters['branch_id']), function ($query) use ($branches) {
            return $query->whereIn('items.branch_id', $branches);
        })->get();



        $materialRequests = MaterialRequest::with(['items.item.unit'])
            ->orderByDesc('request_no')
            ->when(!$isSuperadmin && $userBranchId != 2, function ($query) use ($departmentIds) {
                return $query->whereIn('material_requests.department_id', $departmentIds);
            })
            ->get()
            ->map(function ($mr) {
                return [
                    'id' => $mr->id,
                    'request_no' => $mr->request_no,
                    'department_id' => $mr->department_id,
                    'requirement' => $mr->requirement,
                    'items' => $mr->items->map(function ($it) {
                        return [
                            'id' => $it->id,
                            'item_id' => $it->item_id,
                            'code' => $it->item?->code,
                            'name' => $it->item?->name,
                            'stock' => $it->item?->stock?->last_stock,
                            'unit' => $it->item?->unit?->short_name,
                            'quantity_requested' => $it->quantity_requested,
                            'quantity_approved' => $it->quantity_approved,
                            'note' => $it->note,
                        ];
                    }),
                ];
            });



        return Inertia::render(
            'Admin/PurchaseRequests/Create',
            compact('requestNo', 'materialRequests', 'departments', 'employees', 'items', 'user', 'isSuperadmin')
        );
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'request_id' => ['nullable', 'exists:material_requests,id'],
            'request_no' => ['required', 'string', 'unique:purchase_requests,request_no'],
            'requested_at' => ['required', 'date', 'before_or_equal:today'],
            'department_id' => ['required', 'exists:departments,id'],
            'employee_id' => ['required', 'exists:employees,id'],
            'requirement' => ['nullable', 'string', 'max:255'],
            'requests' => ['required', 'array'],
            'requests.*.item_id' => ['required_with:requests', 'exists:items,id'],
            'requests.*.request' => ['required_with:requests', 'integer', 'min:1'],
            'requests.*.note' => ['nullable', 'string', 'max:225'],
        ]);

        try {
            app(CreatePurchaseRequest::class)->execute($data);

            Log::info('Request created successfully');
            return redirect()->route('purchase-requests.index')->with('success', 'Pengajuan berhasil dibuat!');
        } catch (Exception $e) {
            Log::error('Failed to create request', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'data' => $data
            ]);
            return back()->withInput()->with('error', 'Terjadi kesalahan saat menyimpan pengajuan. Silakan coba lagi.');
        }
    }

    public function show($id)
    {
        $purchaseRequest = PurchaseRequest::with([
            'items.item.unit',
            'department',
            'requester',
            'request'
        ])->findOrFail($id);

        $canApprove = Auth::user()->can('purchase_requests.approve', $purchaseRequest);


        // Format purchase request data for show
        $pr = [
            'id' => $purchaseRequest->id,
            'request_no' => $purchaseRequest->request_no,
            'requested_at' => Carbon::parse($purchaseRequest->requested_at)->format('Y-m-d'),
            'department_id' => $purchaseRequest->department_id,
            'employee_id' => $purchaseRequest->requested_by,
            'request_id' => $purchaseRequest->request_id,
            'requirement' => $purchaseRequest->requirement,
            'reason' => $purchaseRequest->note,
            'status' => $purchaseRequest->status,
            'department' => $purchaseRequest->department,
            'employee' => $purchaseRequest->requester,
            'material_request' => $purchaseRequest->request,
            'items' => $purchaseRequest->items->map(function ($it) {
                return [
                    'id' => $it->id,
                    'item_id' => $it->item_id,
                    'item' => $it->item,
                    'quantity_requested' => $it->quantity_requested,
                    'quantity_approved' => $it->quantity_approved,
                    'note' => $it->note,
                ];
            }),
            'can_approve' => $canApprove,
        ];

        // Get activities for this purchase request
        $activities = PurchaseRequestActivity::where('purchase_request_id', $purchaseRequest->id)
            ->with('createdBy:id,name')
            ->orderByDesc('id')
            ->get()
            ->map(function ($activity) {
                return [
                    'title' => $activity->title,
                    'description' => $activity->description,
                    'time' => optional($activity->created_at)->locale('id')->translatedFormat('d F Y H:i'),
                    'created_by' => $activity->createdBy?->name,
                ];
            });

        return Inertia::render('Admin/PurchaseRequests/Show', [
            'pr' => $pr,
            'activities' => $activities,
            'can_approve' => $canApprove,
        ]);
    }

    public function edit($id)
    {
        $departments = Department::with(['branch'])->orderBy('name')->get();
        $employees = Employee::orderBy('name')->get();
        $items = Item::with(['unit', 'stock:id,item_id,last_stock'])->orderBy('name')->get();
        $materialRequests = MaterialRequest::with(['items.item.unit'])
            ->orderByDesc('request_no')
            ->get()
            ->map(function ($mr) {
                return [
                    'id' => $mr->id,
                    'request_no' => $mr->request_no,
                    'department_id' => $mr->department_id,
                    'requirement' => $mr->requirement,
                    'items' => $mr->items->map(function ($it) {
                        return [
                            'id' => $it->id,
                            'item_id' => $it->item_id,
                            'code' => $it->item?->code,
                            'name' => $it->item?->name,
                            'stock' => $it->item?->stock?->last_stock,
                            'unit' => $it->item?->unit?->short_name,
                            'quantity_requested' => $it->quantity_requested,
                            'quantity_approved' => $it->quantity_approved,
                            'note' => $it->note,
                        ];
                    }),
                ];
            });

        $user = Auth::user();
        $purchaseRequest = PurchaseRequest::with(['items.item.unit', 'items.item.stock'])
            ->findOrFail($id);

        // Format purchase request data for edit
        $pr = [
            'id' => $purchaseRequest->id,
            'request_no' => $purchaseRequest->request_no,
            'requested_at' => Carbon::parse($purchaseRequest->requested_at)->format('Y-m-d'),
            'department_id' => $purchaseRequest->department_id,
            'employee_id' => $purchaseRequest->requested_by,
            'request_id' => $purchaseRequest->request_id,
            'requirement' => $purchaseRequest->requirement,
            'status' => $purchaseRequest->status,
            'items' => $purchaseRequest->items->map(function ($it) {
                return [
                    'id' => $it->id,
                    'item_id' => $it->item_id,
                    'code' => $it->item?->code,
                    'name' => $it->item?->name,
                    'stock' => $it->item?->stock,
                    'unit' => $it->item?->unit,
                    'quantity_requested' => $it->quantity_requested,
                    'note' => $it->note,
                ];
            }),
        ];

        return Inertia::render('Admin/PurchaseRequests/Edit', [
            'pr' => $pr,
            'requestNo' => $purchaseRequest->request_no,
            'materialRequests' => $materialRequests,
            'departments' => $departments,
            'employees' => $employees,
            'items' => $items,
            'user' => $user,
        ]);
    }

    public function update(Request $request, $id)
    {
        try {
            $validated = $request->validate([
                'request_no' => 'required|string|max:255',
                'requested_at' => 'required|date',
                'department_id' => 'required|exists:departments,id',
                'employee_id' => 'required|exists:employees,id',
                'request_id' => 'nullable|exists:material_requests,id',
                'requirement' => 'nullable|string',
                'requests' => 'required|array|min:1',
                'requests.*.item_id' => 'required|exists:items,id',
                'requests.*.request' => 'required|numeric|min:1',
                'requests.*.note' => 'nullable|string|max:255',
            ]);

            DB::transaction(function () use ($validated, $id) {
                $purchaseRequest = PurchaseRequest::findOrFail($id);

                // Update purchase request
                $purchaseRequest->update([
                    'request_no' => $validated['request_no'],
                    'requested_at' => $validated['requested_at'],
                    'department_id' => $validated['department_id'],
                    'employee_id' => $validated['employee_id'],
                    'request_id' => $validated['request_id'],
                    'requirement' => $validated['requirement'],
                ]);

                // Delete existing items
                $purchaseRequest->items()->delete();

                // Create new items
                foreach ($validated['requests'] as $requestItem) {
                    $purchaseRequest->items()->create([
                        'item_id' => $requestItem['item_id'],
                        'quantity_requested' => $requestItem['request'],
                        'note' => $requestItem['note'],
                    ]);
                }
            });

            // activity log
            app(CreatePurchaseLogActivities::class)->execute($id, 'Memperbarui Permintaan', 'Permintaan baru diperbarui dengan nomor ' . $validated['request_no']);

            return redirect()->route('purchase-requests.index')
                ->with('success', 'Pengajuan berhasil diperbarui');
        } catch (Throwable $e) {
            Log::error('Purchase Request update error', [
                'err' => $e->getMessage(),
                'request_id' => $id,
                'data' => $request->all()
            ]);
            return redirect()->back()
                ->withErrors(['error' => 'Gagal memperbarui pengajuan'])
                ->withInput();
        }
    }

    public function destroy(PurchaseRequest $purchaseRequest)
    {
        try {
            // Check if purchase request has blocking relationships
            $blockingRelationships = [];

            // Check if purchase request has purchase order
            if ($purchaseRequest->purchaseOrder()->exists()) {
                $blockingRelationships[] = 'purchase order';
            }

            if (!empty($blockingRelationships)) {
                $message = 'Data pengajuan pembelian tidak dapat dihapus karena masih memiliki relasi dengan data: ' . implode(', ', $blockingRelationships);
                return redirect()->back()->with('error', $message);
            }

            DB::transaction(function () use ($purchaseRequest) {
                // Delete related items first
                $logActivities = $purchaseRequest->logActivities()->get();
                if ($logActivities) {
                    foreach ($logActivities as $logActivity) {
                        $logActivity->delete();
                    }
                }

                $purchaseRequest->items()->delete();

                $purchaseRequest->delete();
            });
            return redirect()->back()->with('success', 'Pengajuan berhasil dihapus');
        } catch (Throwable $e) {
            Log::error('Purchase Request destroy error', ['err' => $e->getMessage()]);

            // Handle specific foreign key constraint errors
            if (str_contains($e->getMessage(), 'Integrity constraint violation') || str_contains($e->getMessage(), 'foreign key constraint fails')) {
                return redirect()->back()->with('error', 'Data pengajuan pembelian tidak dapat dihapus karena masih memiliki relasi dengan data lain.');
            }

            return redirect()->back()->with('error', 'Gagal menghapus pengajuan: ' . $e->getMessage());
        }
    }

    public function approve(Request $request, $id)
    {
        try {
            $validated = $request->validate([
                'approvals' => 'required|array',
                'approvals.*.id' => 'required|exists:purchase_request_items,id',
                'approvals.*.quantity_approved' => 'required|numeric|min:0',
                'approvals.*.note' => 'nullable|string|max:255',
            ]);

            $purchaseRequest = PurchaseRequest::findOrFail($id);

            app(ApprovePurchasedRequest::class)->execute($purchaseRequest, $validated);


            return redirect()->back()->with('success', 'Pengajuan berhasil disetujui');
        } catch (Throwable $e) {
            Log::error('Purchase Request approve error', [
                'err' => $e->getMessage(),
                'request_id' => $id,
                'data' => $request->all()
            ]);
            return redirect()->back()->with('error', 'Gagal menyetujui pengajuan' . $e->getMessage());
        }
    }

    public function rejected(Request $request, $id)
    {
        try {
            DB::transaction(function () use ($id, $request) {
                $purchaseRequest = PurchaseRequest::findOrFail($id);
                $purchaseRequest->update([
                    'status' => 'rejected',
                    'rejected_at' => now(),
                    'rejected_by' => Auth::user()->name,
                    'note' => $request->reason
                ]);
            });
            // activity log
            app(CreatePurchaseLogActivities::class)->execute($id, 'Menolak Permintaan', 'Permintaan ditolak. Status: ditolak, dengan alasan: ' . $request->reason);

            return redirect()->back()->with('success', 'Pengajuan berhasil ditolak');
        } catch (Throwable $e) {
            Log::error('Purchase Request rejected error', [
                'err' => $e->getMessage(),
                'request_id' => $id
            ]);
            return redirect()->back()->with('error', 'Gagal menolak pengajuan');
        }
    }

    public function cancel($id)
    {
        try {
            DB::transaction(function () use ($id) {
                $purchaseRequest = PurchaseRequest::findOrFail($id);
                $purchaseRequest->update([
                    'status' => 'canceled',
                    'canceled_at' => now(),
                    'canceled_by' => Auth::user()->name,
                ]);
            });

            // activity log
            app(CreatePurchaseLogActivities::class)->execute($id, 'Membatalkan Permintaan', 'Permintaan dibatalkan. Status: dibatalkan');

            return redirect()->back()->with('success', 'Pengajuan berhasil dibatalkan');
        } catch (Throwable $e) {
            Log::error('Purchase Request cancel error', [
                'err' => $e->getMessage(),
                'request_id' => $id
            ]);
            return redirect()->back()->with('error', 'Gagal membatalkan pengajuan');
        }
    }
}
