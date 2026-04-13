<?php

namespace App\Http\Controllers;

use App\Actions\Data\MaterialRequest\ApproveMaterialRequest;
use Exception;
use App\Models\Item;
use Inertia\Inertia;
use App\Models\Employee;
use App\Models\Department;
use Illuminate\Http\Request;
use App\Models\MaterialRequest;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use App\Models\MaterialRequestActivity;
use Carbon\Carbon;

class MaterialRequestController extends Controller
{
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

        if ($groupBy === 'all') {
            $groupBy = null;
        }
        $isSuperadmin = Auth::user()->hasRole("Superadmin");
        $userBranchId = Auth::user()->employee->branch_id ?? null;

        $departments = Department::where('branch_id', $userBranchId)->pluck('id');

        $query = MaterialRequest::query()
            ->with(['department:id,name', 'requester:id,name', 'approver:id,name'])
            ->when(!$isSuperadmin && $userBranchId != 2, function ($query) use ($departments) {
                return $query->whereIn('department_id', $departments);
            });

        // Apply filters
        if ($filters['q']) {
            $query->where(function ($q) use ($filters) {
                $search = $filters['q'];
                $q->where('request_no', 'like', "%{$search}%")
                    ->orWhereHas('requester', function ($qr) use ($search) {
                        return $qr->where('name', 'like', "%{$search}%");
                    })
                    ->orWhereHas('department', function ($qd) use ($search) {
                        return $qd->where('name', 'like', "%{$search}%");
                    })
                    ->orWhere('status', 'like', "%{$search}%");
                // ->orWhereRaw("DATE_FORMAT(requested_at, '%Y-%m-%d') like ?", ["%{$search}%"])
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
                    ->whereColumn('departments.id', 'material_requests.department_id')
            );
        }
        if ($groupBy === 'requested_by') {
            $query->orderBy(
                Employee::select('name')
                    ->whereColumn('employees.id', 'material_requests.requested_by')
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
                    ->whereColumn('departments.id', 'material_requests.department_id'),
                $sortDirection
            );
        } elseif ($sortBy === 'requested_by') {
            $query->orderBy(
                Employee::select('name')
                    ->whereColumn('employees.id', 'material_requests.requested_by'),
                $sortDirection
            );
        } else {
            // Default: order by request number (latest first)
            $query->orderBy('created_at', 'desc');
        }

        $perPage = $request->integer('per_page', 20);
        $materialRequests = $query->orderBy('created_at', 'desc')->paginate($perPage)->withQueryString();

        // Reflect the effective status back to the UI
        $effectiveStatus = $request->has('status') ? ($filters['status'] ?? '') : null;

        $canEdit = Auth::user()->can('material_requests.edit');
        $canDelete = Auth::user()->can('material_requests.delete');
        $canView = Auth::user()->can('material_requests.view');
        $canCreate = Auth::user()->can('material_requests.create');


        $departments = Department::query()->when(!$isSuperadmin && $userBranchId != 2, function ($query) use ($departments) {
            return $query->whereIn('id', $departments);
        })->select('id', 'name')->orderBy('name')->get();

        $employees = Employee::query()->when(!$isSuperadmin && $userBranchId != 2, function ($query) use ($departments) {
            return $query->whereIn('department_id', $departments);
        })->select('id', 'name')->orderBy('name')->get();

        return Inertia::render('Admin/MaterialRequests/Index', [
            'can_edit' => $canEdit,
            'can_delete' => $canDelete,
            'can_view' => $canView,
            'can_create' => $canCreate,
            'materialRequests' => $materialRequests->through(function ($mr) {
                return [
                    'id' => $mr->id,
                    'request_no' => $mr->request_no,
                    'department' => $mr->department?->name,
                    'requested_by' => $mr->requester?->name,
                    'requested_at' => Carbon::parse($mr->requested_at)->locale('id')->translatedFormat('d F Y'),
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
                'total' => MaterialRequest::count(),
                'on_progress' => MaterialRequest::where('status', 'on_progress')->count(),
            ],
        ]);
    }

    public function create()
    {
        $requestNo = MaterialRequest::generateRequestNo();
        $departments = Department::with(['branch'])->orderBy('name')->get();
        $employees = Employee::orderBy('name')->get();

        // Get user's department branch to filter items
        $user = Auth::user();
        $userBranchId = $user->employee?->department?->branch_id;

        // Filter items based on user's department branch
        $items = Item::with(['unit', 'stock:id,item_id,last_stock'])
            ->when($userBranchId && $userBranchId != 2, function ($query) use ($userBranchId) {
                return $query->where('branch_id', $userBranchId);
            })
            ->orderBy('name')
            ->get()
            ->map(function ($item) {
                return [
                    'id' => $item->id,
                    'name' => $item->name,
                    'code' => $item->code,
                    'stock' => $item->stock?->last_stock ?? 0,
                    'unit' => $item->unit,
                ];
            });

        return Inertia::render('Admin/MaterialRequests/Create', [
            'requestNo' => $requestNo,
            'departments' => $departments,
            'employees' => $employees,
            'items' => $items,
            'user' => [
                'department' => $user->employee?->department,
                'employee' => $user->employee,
                'roles' => $user->roles,
            ],
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'request_no' => ['required', 'string', 'unique:material_requests,request_no'],
            'requested_at' => ['required', 'date'],
            'department_id' => ['required', 'exists:departments,id'],
            'employee_id' => ['required', 'exists:employees,id'],
            'requirement' => ['nullable', 'string'],
            'requests' => ['required', 'array'],
            'requests.*.item_id' => ['required_with:requests', 'exists:items,id'],
            'requests.*.request' => ['required_with:requests', 'integer', 'min:1'],
            'requests.*.note' => ['nullable', 'string', 'max:225'],
        ]);

        try {
            DB::transaction(function () use ($data) {
                $materialRequest = MaterialRequest::create([
                    'request_no' => $data['request_no'],
                    'requested_at' => $data['requested_at'],
                    'requested_by' => $data['employee_id'],
                    'department_id' => $data['department_id'],
                    'requirement' => $data['requirement'] ?? null,
                    'status' => 'pending'
                ]);

                foreach ($data['requests'] as $r) {
                    $materialRequest->items()->create([
                        'item_id' => $r['item_id'],
                        'quantity_requested' => $r['request'],
                        'note' => $r['note'] ?? null,
                    ]);
                }

                // activity log
                $this->logActivity($materialRequest->id, 'Membuat Permintaan', 'Permintaan baru dibuat dengan nomor ' . $materialRequest->request_no);
            });

            Log::info('Request created successfully');
            return redirect()->route('material-requests.index')->with('success', 'Permintaan berhasil dibuat!');
        } catch (Exception $e) {
            Log::error('Failed to create request', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'data' => $data
            ]);
            return back()->withInput()->with('error', 'Terjadi kesalahan saat menyimpan permintaan. Silakan coba lagi.');
        }
    }

    public function show(MaterialRequest $materialRequest)
    {
        $materialRequest->load([
            'department:id,name',
            'requester:id,name',
            'approver:id,name',
            'items.item.unit:id,name,short_name',
        ]);

        //cek permission good_receipts.approve
        $canApprove = Auth::user()->can('material_requests.approve');

        $activities = MaterialRequestActivity::where('material_request_id', $materialRequest->id)
            ->orderByDesc('id')
            ->get()
            ->map(function ($a) {
                return [
                    'title' => $a->title,
                    'description' => $a->description,
                    'time' => optional($a->created_at)->locale('id')->translatedFormat('d F Y H:i'),
                ];
            });

        $canApprove = Auth::user()->can('material_requests.approve');


        return Inertia::render('Admin/MaterialRequests/Show', [
            'mr' => [
                'id' => $materialRequest->id,
                'request_no' => $materialRequest->request_no,
                'department' => $materialRequest->department?->name,
                'requested_by' => $materialRequest->requester?->name,
                'requested_at' => optional($materialRequest->requested_at)->format('Y-m-d'),
                'approved_by' => $materialRequest->approver?->name,
                'approved_at' => optional($materialRequest->approved_at)?->format('Y-m-d'),
                'requirement' => $materialRequest->requirement,
                'note' => $materialRequest->note,
                'status' => $materialRequest->status,
            ],
            'items' => $materialRequest->items->map(function ($item) {
                return [
                    'id' => $item->id,
                    'item_name' => $item->item?->name,
                    'item_code' => $item->item?->code,
                    'quantity_requested' => $item->quantity_requested,
                    'quantity_approved' => $item->quantity_approved,
                    'note' => $item->note,
                    'unit' => $item->item?->unit?->short_name,
                ];
            }),
            'activities' => $activities,
            'can_approve' => $canApprove,
        ]);
    }

    public function edit(MaterialRequest $materialRequest)
    {
        $materialRequest->load([
            'department:id,name',
            'requester:id,name',
            'approver:id,name',
            'items.item.unit:id,name,short_name',
        ]);

        // Get all items for the select options
        $allItems = Item::with(['unit', 'stock:id,item_id,last_stock'])->orderBy('name')->get();

        return Inertia::render('Admin/MaterialRequests/Edit', [
            'mr' => [
                'id' => $materialRequest->id,
                'request_no' => $materialRequest->request_no,
                'department_id' => $materialRequest->department_id,
                'employee_id' => $materialRequest->requested_by,
                'requested_at' => optional($materialRequest->requested_at)->format('Y-m-d'),
                'status' => $materialRequest->status,
                'requirement' => $materialRequest->requirement,
            ],
            'items' => $materialRequest->items->map(function ($it) {
                return [
                    'id' => $it->id,
                    'item_id' => $it->item?->id,
                    'item_name' => $it->item?->name,
                    'item_code' => $it->item?->code,
                    'qty' => $it->quantity_requested,
                    'note' => $it->note,
                    'unit' => $it->item?->unit?->short_name,
                ];
            }),
            'allItems' => $allItems->map(function ($item) {
                return [
                    'id' => $item->id,
                    'name' => $item->name,
                    'code' => $item->code,
                    'stock' => $item->stock?->last_stock ?? 0,
                    'unit' => $item->unit,
                ];
            }),
            'departments' => Department::query()->select('id', 'name')->orderBy('name')->get(),
            'employees' => Employee::query()->select('id', 'name')->orderBy('name')->get(),
            'statusOptions' => [
                ['value' => 'on_progress', 'label' => 'Menunggu Persetujuan'],
                ['value' => 'approved', 'label' => 'Disetujui'],
                ['value' => 'rejected', 'label' => 'Ditolak'],
                ['value' => 'cancelled', 'label' => 'Dibatalkan'],
                ['value' => 'partial_approved', 'label' => 'Disetujui Sebagian'],
            ],
            'user' => [
                'department' => Auth::user()->employee?->department,
                'employee' => Auth::user()->employee,
                'roles' => Auth::user()->roles,
            ],
        ]);
    }

    public function update(Request $request, MaterialRequest $materialRequest)
    {
        $data = $request->validate([
            'request_no' => ['required', 'string', 'max:50', 'unique:material_requests,request_no,' . $materialRequest->id],
            'department_id' => ['required', 'exists:departments,id'],
            'employee_id' => ['required', 'exists:employees,id'],
            'requested_at' => ['required', 'date'],
            'status' => ['nullable', 'in:on_progress,approved,rejected,cancelled,partial_approved'],
            'requirement' => ['nullable', 'string'],
            'requests' => ['required', 'array'],
            'requests.*.item_id' => ['required_with:requests', 'exists:items,id'],
            'requests.*.request' => ['required_with:requests', 'integer', 'min:1'],
            'requests.*.note' => ['nullable', 'string', 'max:225'],
        ]);

        $data['requested_by'] = $data['employee_id'];
        $materialRequest->update($data);

        $materialRequest->items()->delete();

        foreach ($data['requests'] as $r) {
            $materialRequest->items()->create([
                'item_id' => $r['item_id'],
                'quantity_requested' => $r['request'],
                'note' => $r['note'] ?? null,
            ]);
        }

        $this->logActivity($materialRequest->id, 'Memperbarui Permintaan', 'Data permintaan diperbarui.');

        return redirect()
            ->route('material-requests.index')
            ->with('success', 'Permintaan berhasil diperbarui.');
    }

    public function destroy(MaterialRequest $materialRequest)
    {
        $id = $materialRequest->id;
        $requestNo = $materialRequest->request_no;
        $materialRequest->delete();
        $this->logActivity($id, 'Menghapus Permintaan', 'Permintaan ' . $requestNo . ' dihapus.');
        return redirect()
            ->route('material-requests.index')
            ->with('success', 'Permintaan berhasil dihapus.');
    }

    public function rejected(MaterialRequest $materialRequest)
    {
        $materialRequest->update([
            'status' => 'rejected',
            'approved_at' => now(),
            'approved_by' => Auth::user()->id,
        ]);
        $this->logActivity($materialRequest->id, 'Menolak Permintaan', 'Permintaan ditolak.');
        return redirect()
            ->route('material-requests.index')
            ->with('success', 'Penolakan permintaan berhasil.');
    }

    public function approve(Request $request, MaterialRequest $materialRequest)
    {
        app(ApproveMaterialRequest::class)->execute($materialRequest, $request->all());

        $this->logActivity($materialRequest->id, 'Menyetujui Permintaan', 'Permintaan disetujui beserta jumlah yang disetujui.');
        return redirect()->route('material-requests.index')->with('success', 'Permintaan disetujui.');
    }

    public function issue(MaterialRequest $materialRequest)
    {
        // Placeholder: mark as partial_approved to reflect barang dikeluarkan sebagian/awal
        $materialRequest->update([
            'status' => 'partial_approved',
        ]);
        $this->logActivity($materialRequest->id, 'Mulai Pengeluaran', 'Proses pengeluaran barang dimulai.');
        return back()->with('success', 'Proses pengeluaran barang dimulai.');
    }

    public function cancel(MaterialRequest $materialRequest)
    {
        $materialRequest->update([
            'status' => 'cancelled',
        ]);
        $this->logActivity($materialRequest->id, 'Membatalkan Permintaan', 'Permintaan dibatalkan.');
        return redirect()->route('material-requests.index')->with('success', 'Permintaan dibatalkan.');
    }

    public function getMaterialRequests(Request $request)
    {
        $departmentId = $request->integer('department_id');

        if (!$departmentId) {
            return response()->json(['materialRequests' => []]);
        }

        $department = Department::with('branch')->find($departmentId);

        if (!$department || !$department->branch_id) {
            return response()->json(['materialRequests' => []]);
        }

        // Get all departments with the same branch_id
        $departmentIds = Department::where('branch_id', $department->branch_id)->pluck('id');

        // Get Material Requests from departments in the same branch
        $materialRequests = MaterialRequest::whereIn('department_id', $departmentIds)
            ->where('status', 'approved')
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($mr) {
                return [
                    'id' => $mr->id,
                    'request_no' => $mr->request_no,
                ];
            });

        return response()->json(['materialRequests' => $materialRequests]);
    }

    public function getItemsByDepartment(Request $request)
    {
        $departmentId = $request->integer('department_id');

        if (!$departmentId) {
            return response()->json(['items' => []]);
        }

        $department = Department::with('branch')->find($departmentId);

        if (!$department || !$department->branch_id) {
            return response()->json(['items' => []]);
        }

        $items = Item::with(['unit', 'stock:id,item_id,last_stock'])
            ->where('branch_id', $department->branch_id)
            ->orderBy('name')
            ->get()
            ->map(function ($item) {
                return [
                    'id' => $item->id,
                    'name' => $item->name,
                    'code' => $item->code,
                    'stock' => $item->stock?->last_stock ?? 0,
                    'unit' => $item->unit,
                ];
            });

        return response()->json(['items' => $items]);
    }

    public function getItemsByRequest(Request $request)
    {
        $requestId = $request->integer('request_id');

        if (!$requestId) {
            return response()->json(['items' => []]);
        }

        $materialRequest = MaterialRequest::with(['items.item.unit', 'items.item.stock'])
            ->find($requestId);

        if (!$materialRequest) {
            return response()->json(['items' => []]);
        }

        $items = $materialRequest->items->map(function ($mrItem) {
            return [
                'id' => $mrItem->item->id,
                'name' => $mrItem->item->name,
                'code' => $mrItem->item->code,
                'stock' => $mrItem->item->stock?->last_stock ?? 0,
                'unit' => $mrItem->item->unit,
                'quantity_requested' => $mrItem->quantity_requested,
                'quantity_approved' => $mrItem->quantity_approved,
                'note' => $mrItem->note,
            ];
        });

        return response()->json(['items' => $items]);
    }

    private function logActivity($materialRequestId, $title, $description)
    {
        try {
            MaterialRequestActivity::create([
                'material_request_id' => $materialRequestId,
                'title' => $title,
                'description' => $description,
                'created_by' => Auth::id(),
            ]);
        } catch (\Throwable $e) {
            Log::warning('Failed to log MR activity', ['mr_id' => $materialRequestId, 'error' => $e->getMessage()]);
        }
    }
}
