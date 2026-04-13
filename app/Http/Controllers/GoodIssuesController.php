<?php

namespace App\Http\Controllers;

use App\Actions\Data\GoodIssue\ApproveGoodIssue;
use Exception;
use App\Models\Item;
use Inertia\Inertia;
use App\Models\Employee;
use App\Models\GoodIssue;
use App\Models\ItemStock;
use App\Models\Department;
use App\Models\GoodIssueActivity;
use Illuminate\Http\Request;
use App\Models\MaterialRequest;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;

class GoodIssuesController extends Controller
{
    public function index0(Request $request)
    {
        abort_unless(Gate::allows('good_issues.view'), 403, 'Anda tidak memiliki akses untuk melihat pemakaian barang');

        $filters = [
            'q' => (string) $request->get('q', ''),
            'department_id' => $request->integer('department_id') ?: null,
            'employee_id' => $request->integer('employee_id') ?: null,
            'status' => $request->input('status') ?: '',
            'date_from' => $request->input('date_from'),
            'date_to' => $request->input('date_to'),
        ];

        $goodIssues = [
            [
                'id' => 1,
                'number' => 'IU-' . now()->format('Y') . '-0001',
                'date' => now()->toDateString(),
                'created_by' => ['id' => 1, 'name' => 'John Doe'],
                'department' => 'Finance',
                'reference' => ['type' => 'material_request', 'id' => 3, 'number' => 'MR-' . now()->format('Y') . '-0003'],
                'status' => 'draft',
                'total_items' => 4,
            ],
        ];

        return Inertia::render('Admin/GoodIssues/Index', [
            'goodIssues' => [
                'data' => $goodIssues,
                'current_page' => 1,
                'per_page' => 15,
            ],
            'filters' => $filters,
            'departments' => Department::select('id', 'name')->orderBy('name')->get(),
            'employees' => Employee::select('id', 'name')->orderBy('name')->limit(200)->get(),
        ]);
    }

    public function index(Request $request)
    {
        abort_unless(Gate::allows('good_issues.view'), 403, 'Anda tidak memiliki akses untuk melihat pemakaian barang');

        $filters = [
            'q' => $request->string('q')->toString(),
            'date_from' => $request->string('date_from')->toString(),
            'date_to' => $request->string('date_to')->toString(),
            'department_id' => $request->integer('department_id') ?: null,
            'status' => $request->has('status') ? $request->string('status')->toString() : null,
        ];

        $sortBy = $request->get('sortBy', 'created_at');
        $sortDirection = $request->get('sortDirection', 'desc');
        $groupBy = $request->get('groupBy');

        $isSuperadmin = Auth::user()->hasRole('Superadmin');
        $departments = Department::where('branch_id', Auth::user()->employee->branch_id)->pluck('id');

        $query = GoodIssue::query()
            ->with(['department:id,name', 'request:id,request_no', 'requestBy:id,name'])
            ->withCount('items')
            ->when(!$isSuperadmin && Auth::user()->employee->branch_id != 2, function ($query) use ($departments) {
                return $query->whereIn('department_id', $departments);
            });

        if ($filters['q']) {
            $query->where(function ($q) use ($filters) {
                $search = $filters['q'];
                $q->where('number', 'like', "%{$search}%")
                    ->orWhereHas('request', fn($qr) => $qr->where('request_no', 'like', "%{$search}%"));
            });
        }

        if ($filters['date_from']) {
            $query->whereDate('created_at', '>=', $filters['date_from']);
        }
        if ($filters['date_to']) {
            $query->whereDate('created_at', '<=', $filters['date_to']);
        }

        if ($filters['department_id']) {
            $query->where('department_id', $filters['department_id']);
        }

        if ($filters['status']) {
            $query->where('status', $filters['status']);
        }

        $sortDirection = strtolower($sortDirection) === 'asc' ? 'asc' : 'desc';

        if ($sortBy === 'date') {
            $query->orderBy('created_at', $sortDirection);
        } elseif ($sortBy === 'department') {
            $query->orderBy(
                Department::select('name')
                    ->whereColumn('departments.id', 'good_issues.department_id'),
                $sortDirection
            );
        } elseif ($sortBy === 'status') {
            $query->orderBy('status', $sortDirection);
        } else {
            $query->orderBy('created_at', $sortDirection);
        }

        $perPage = $request->integer('per_page', 20);
        $goodIssues = $query->paginate($perPage)->withQueryString();

        $effectiveStatus = $request->has('status') ? ($filters['status'] ?? '') : null;

        return Inertia::render('Admin/GoodIssues/Index', [
            'goodIssues' => $goodIssues->through(function ($gi) {
                return [
                    'id' => $gi->id,
                    'number' => $gi->number,
                    'request' => $gi->request?->request_no,
                    'department' => $gi->department?->name,
                    'created_by' => $gi->requestBy?->name,
                    'date' => $gi->created_at->locale('id')->translatedFormat('d F Y'),
                    'note' => $gi->note,
                    'status' => $gi->status,
                    'items_count' => $gi->items_count,
                ];
            }),
            'departments' => Department::query()->select('id', 'name')->orderBy('name')->get(),
            'filters' => array_merge($filters, ['status' => $effectiveStatus]),
            'sortBy' => $sortBy,
            'sortDirection' => $sortDirection,
            'groupBy' => in_array($groupBy, ['date', 'department', 'status'], true) ? $groupBy : null,
            'sidebarCounts' => [
                'total' => GoodIssue::count(),
                'on_progress' => GoodIssue::where('status', 'draft')->count(),
            ],
        ]);
    }

    public function create()
    {
        abort_unless(Gate::allows('good_issues.create'), 403, 'Anda tidak memiliki akses untuk menambah pemakaian barang');

        $departments = Department::with(['branch'])->orderBy('name')->get();
        $isSuperadmin = Auth::user()->hasRole('Superadmin');
        $departmentIds = Department::where('id', Auth::user()->employee->branch_id)->pluck('id');
        $materialRequests = MaterialRequest::doesntHave('goodIssues')
            ->when(!$isSuperadmin && Auth::user()->employee->branch_id != 2, function ($query) use ($departmentIds) {
                return $query->whereIn('department_id', $departmentIds);
            })
            ->orderBy('request_no', 'desc')
            ->get();

        $items = Item::with(['unit'])->when(!$isSuperadmin, function ($query) use ($departmentIds) {
            return $query->whereIn('branch_id', $departmentIds);
        })->orderBy('name')->get();

        $user = Auth::user();
        if ($user->employee) {
            $user->employee->load(['department.branch']);
        }

        return Inertia::render(
            'Admin/GoodIssues/Create',
            compact('departments', 'materialRequests', 'items', 'user', 'isSuperadmin')
        );
    }

    public function store(Request $request)
    {
        abort_unless(Gate::allows('good_issues.create'), 403, 'Anda tidak memiliki akses untuk menambah pemakaian barang');

        $data = $request->validate([
            'request_id' => ['nullable', 'exists:material_requests,id'],
            'department_id' => ['required', 'exists:departments,id'],
            'date' => ['required', 'date'],
            'requirement' => ['nullable', 'string'],
            'items' => ['required', 'array'],
            'items.*.item_id' => ['required_with:items', 'exists:items,id'],
            'items.*.amount' => ['required_with:items', 'integer', 'min:1'],
            'items.*.note' => ['nullable', 'string', 'max:225'],
        ]);

        try {
            DB::transaction(function () use ($data) {
                $goodIssue = GoodIssue::create([
                    'request_id' => $data['request_id'],
                    'department_id' => $data['department_id'],
                    'status' => 'pending',
                    'request_by' => Auth::user()->employee->id,
                    'date' => $data['date'],
                    'requirement' => $data['requirement'] ?? null,
                ]);

                foreach ($data['items'] as $r) {
                    $goodIssue->items()->create([
                        'item_id' => $r['item_id'],
                        'quantity_issued' => $r['amount'],
                        'note' => $r['note'] ?? null,
                    ]);
                }

                $this->logActivity($goodIssue->id, 'Membuat Pemakaian Barang', "Pemakaian Barang {$goodIssue->kode_usage} dibuat.");
            });

            return redirect()->route('good-issues.index')->with('success', 'Pemakaian Barang berhasil dibuat!');
        } catch (Exception $e) {
            Log::error('Terjadi kesalahan saat menyimpan pemakaian barang', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'data' => $data
            ]);
            return back()->withInput()->with('error', 'Terjadi kesalahan saat menyimpan log. Silakan coba lagi.');
        }
    }

    public function show(GoodIssue $goodIssue)
    {
        abort_unless(Gate::allows('good_issues.view'), 403, 'Anda tidak memiliki akses untuk melihat pemakaian barang');

        $goodIssue->load([
            'department',
            'requestBy',
            'approvedBy',
            'request.department',
            'request.requester',
            'request.approver',
            'items.item.unit',
            'items.item.stock',
            'activities.createdBy:id,name',
        ]);

        $canApprove = Auth::user()->can('good_issues.approve');

        return Inertia::render('Admin/GoodIssues/Show', [
            'mr' => [
                'id' => $goodIssue->id,
                'request_no' => $goodIssue->kode_usage ?? '',
                'number' => $goodIssue->kode_usage ?? '',
                'department' => $goodIssue->department?->name ?? '',
                'requested_by' => $goodIssue->requestBy?->name ?? '',
                'approved_by' => $goodIssue->approvedBy?->name ?? '',
                'approved_at' => $goodIssue->approved_at ? Carbon::parse($goodIssue->approved_at)->format('Y-m-d H:i:s') : '',
                'request_date' => $goodIssue->date ? Carbon::parse($goodIssue->date)->format('Y-m-d') : '',
                'status' => $goodIssue->status ?? 'draft',
                'notes' => $goodIssue->requirement ?? '',
            ],
            'items' => $goodIssue->items->map(function ($item) {
                // Get current stock
                $currentStock = $item->item?->stock?->last_stock ?? 0;

                return [
                    'id' => $item->id,
                    'item_id' => $item->item_id,
                    'item_name' => $item->item?->name ?? '',
                    'item_code' => $item->item?->code ?? '',
                    'qty' => $item->quantity_issued ?? 0,
                    'quantity_approved' => $item->quantity_approved,
                    'note_received' => $item->note_received,
                    'unit' => $item->item?->unit?->name ?? '',
                    'notes' => $item->note ?? '',
                    'current_stock' => $currentStock,
                ];
            }),
            'activities' => $goodIssue->activities->map(function ($activity) {
                return [
                    'title' => $activity->title,
                    'description' => $activity->description,
                    'time' => Carbon::parse($activity->created_at)->format('d M Y, H:i'),
                    'user' => $activity->createdBy?->name ?? 'System',
                ];
            }),
            'can_approve' => $canApprove,
        ]);
    }

    public function approve(Request $request, GoodIssue $goodIssue)
    {
        abort_unless(Gate::allows('good_issues.approve'), 403, 'Anda tidak memiliki akses untuk menyetujui pemakaian barang');

        try {
            $validated = $request->validate([
                'approvals' => 'required|array',
                'approvals.*.id' => 'required|exists:good_issue_items,id',
                'approvals.*.quantity_approved' => 'required|integer|min:0',
                'approvals.*.note_received' => 'nullable|string|max:255',
            ]);

            app(ApproveGoodIssue::class)->execute($goodIssue, $validated['approvals']);

            $this->logActivity($goodIssue->id, 'Menyetujui Pemakaian Barang', "Pemakaian Barang {$goodIssue->kode_usage} disetujui.");

            return redirect()->back()->with('success', 'Good Issue berhasil disetujui!');
        } catch (Exception $e) {
            Log::error('Terjadi kesalahan saat menyetujui good issue', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'good_issue_id' => $goodIssue->id
            ]);
            return back()->with('error', 'Terjadi kesalahan saat menyetujui good issue. Silakan coba lagi.');
        }
    }

    public function edit($id)
    {
        abort_unless(Gate::allows('good_issues.edit'), 403, 'Anda tidak memiliki akses untuk mengubah pemakaian barang');

        $goodIssue = GoodIssue::with(['department', 'requestBy', 'request.department', 'request.requester', 'request.approver', 'items.item.unit'])->find($id);

        if (!$goodIssue) {
            abort(404, 'Good Issue not found');
        }

        $departments = Department::with(['branch'])->orderBy('name')->get();
        $materialRequests = MaterialRequest::doesntHave('goodIssues')
            ->orderBy('request_no', 'desc')
            ->get();

        $isSuperadmin = Auth::user()->hasRole('Superadmin');

        return Inertia::render('Admin/GoodIssues/Edit', [
            'mr' => [
                'id' => $goodIssue->id,
                'reference_request_id' => $goodIssue->request_id,
                'request_date' => $goodIssue->date ? Carbon::parse($goodIssue->date)->format('Y-m-d') : '',
                'department_id' => $goodIssue->department_id,
                'request_by' => $goodIssue->request_by,
                'status' => $goodIssue->status ?? 'draft',
                'notes' => $goodIssue->requirement ?? '',
            ],
            'items' => $goodIssue->items->map(function ($item) {
                return [
                    'id' => $item->id,
                    'item_id' => $item->item_id,
                    'item_name' => $item->item?->name ?? '',
                    'item_code' => $item->item?->code ?? '',
                    'qty' => $item->quantity_issued ?? 0,
                    'amount' => $item->quantity_issued ?? 0,
                    'stock' => $item->item?->stock ?? 0,
                    'unit' => $item->item?->unit ?? (object)[],
                    'notes' => $item->note ?? '',
                    'note' => $item->note ?? '',
                ];
            }),
            'departments' => $departments,
            'materialRequests' => $materialRequests,
            'isSuperadmin' => $isSuperadmin,
        ]);
    }

    public function update(Request $request, $id)
    {
        abort_unless(Gate::allows('good_issues.edit'), 403, 'Anda tidak memiliki akses untuk mengubah pemakaian barang');

        $goodIssue = GoodIssue::findOrFail($id);

        $data = $request->validate([
            'request_id' => ['nullable', 'exists:material_requests,id'],
            'department_id' => ['required', 'exists:departments,id'],
            'date' => ['required', 'date'],
            'requirement' => ['nullable', 'string'],
            'items' => ['required', 'array'],
            'items.*.item_id' => ['required_with:items', 'exists:items,id'],
            'items.*.amount' => ['required_with:items', 'integer', 'min:1'],
            'items.*.note' => ['nullable', 'string', 'max:225'],
        ]);

        try {
            DB::transaction(function () use ($data, $goodIssue) {
                $goodIssue->update([
                    'request_id' => $data['request_id'],
                    'department_id' => $data['department_id'],
                    'date' => $data['date'],
                    'requirement' => $data['requirement'] ?? null,
                ]);

                $goodIssue->items()->delete();

                foreach ($data['items'] as $itemData) {
                    $goodIssue->items()->create([
                        'item_id' => $itemData['item_id'],
                        'quantity_issued' => $itemData['amount'],
                        'note' => $itemData['note'] ?? null,
                    ]);

                    $lastLogItemStock = ItemStock::where('item_id', $itemData['item_id'])
                        ->latest('id')
                        ->first();

                    $initialStock = $lastLogItemStock?->last_stock ?? 0;
                    $lastStock = $initialStock - $itemData['amount'];

                    ItemStock::create([
                        'item_id' => $itemData['item_id'],
                        'type' => 'Out',
                        'source_type' => GoodIssue::class,
                        'source_id' => $goodIssue->id,
                        'initial_stock' => $initialStock,
                        'amount' => $itemData['amount'],
                        'last_stock' => $lastStock,
                    ]);
                }
            });

            $this->logActivity($goodIssue->id, 'Memperbarui Good Issue', "Good Issue {$goodIssue->kode_usage} diperbarui.");

            return redirect()->route('good-issues.index')->with('success', 'Good Issue berhasil diperbarui!');
        } catch (Exception $e) {
            Log::error('Terjadi kesalahan saat memperbarui good issue', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'data' => $data
            ]);
            return back()->withInput()->with('error', 'Terjadi kesalahan saat memperbarui good issue. Silakan coba lagi.');
        }
    }

    public function destroy($id)
    {
        abort_unless(Gate::allows('good_issues.delete'), 403, 'Anda tidak memiliki akses untuk menghapus pemakaian barang');

        $goodIssue = GoodIssue::findOrFail($id);

        $this->logActivity($goodIssue->id, 'Menghapus Pemakaian Barang', "Pemakaian Barang {$goodIssue->kode_usage} dihapus.");

        $goodIssue->delete();
        return redirect()->route('good-issues.index')->with('success', 'Pemakaian Barang berhasil dihapus!');
    }

    private function logActivity($goodIssueId, $title, $description)
    {
        try {
            GoodIssueActivity::create([
                'good_issue_id' => $goodIssueId,
                'title' => $title,
                'description' => $description,
                'created_by' => Auth::id(),
            ]);
        } catch (\Throwable $e) {
            Log::warning('Failed to log Pemakaian Barang activity', [
                'good_issue_id' => $goodIssueId,
                'error' => $e->getMessage()
            ]);
        }
    }
}
