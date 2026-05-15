<?php

namespace App\Http\Controllers;

use App\Actions\Data\GoodReceipt\CreateGoodReceipt;
use App\Actions\Data\Item\UpdateStockItem;
use App\Actions\Data\PurchaseOrder\CreatePurchaseOrderLogActivities;
use App\Models\Branch;
use App\Models\Department;
use Exception;
use Throwable;
use App\Models\Employee;
use App\Models\GoodReceipt;
use App\Models\GoodReceiptItem;
use App\Models\Item;
use App\Models\PurchaseOrder;
use App\Models\PurchaseRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use Illuminate\Support\Facades\Gate;

class PurchaseOrderController extends Controller
{
    public function index(Request $request)
    {
        abort_unless(Gate::allows('purchase_orders.view'), 403, 'Anda tidak memiliki akses untuk melihat data pembelian');

        $perPage = $request->get('per_page', $request->get('perPage', 20));
        $perPage = max(1, min(100, (int) $perPage)); // Limit between 1 and 100

        $isSuperadmin = Auth::user()->hasRole("Superadmin");
        $userBranchId = Auth::user()->employee?->branch_id;

        $filters = [
            'q' => $request->string('q')->toString(),
            'employee_id' => $request->integer('employee_id') ?: null,
            'status_invoice' => $request->string('status_invoice')->toString() ?? PurchaseOrder::STATUS_INVOICE_BELUM_LUNAS,
            'status_delivered' => $request->string('status_delivered')->toString() ?? PurchaseOrder::STATUS_DELIVERED_PENDING,
            'date_from' => $request->string('date_from')->toString(),
            'date_to' => $request->string('date_to')->toString(),
        ];

        $sortBy = $request->get('sortBy', 'created_at');
        $sortDirection = $request->get('sortDirection', 'desc');
        $groupBy = $request->get('groupBy');

        $query = PurchaseOrder::query()
            ->with(['request:id,request_no', 'applicant:id,name', 'branch:id,name', 'items.item.unit']);

        // Filter by branch for non-superadmin users (except branch_id 2)
        if (!$isSuperadmin && $userBranchId && $userBranchId != 2) {
            $query->where('branch_id', $userBranchId);
        }

        // Apply filters
        if ($filters['q']) {
            $query->where(function ($q) use ($filters) {
                $search = $filters['q'];
                $q->where('order_no', 'like', "%{$search}%")
                    ->orWhere('vendor', 'like', "%{$search}%")
                    ->orWhereHas('request', fn($qr) => $qr->where('request_no', 'like', "%{$search}%"));
            });
        }

        // Other filters
        if ($filters['employee_id']) $query->where('ordered_by', $filters['employee_id']);
        if ($filters['status_invoice']) $query->where('status_invoice', $filters['status_invoice']);
        if ($filters['status_delivered']) $query->where('status_delivered', $filters['status_delivered']);
        if ($filters['date_from']) $query->whereDate('ordered_at', '>=', $filters['date_from']);
        if ($filters['date_to']) $query->whereDate('ordered_at', '<=', $filters['date_to']);

        // Sorting
        $sortDirection = strtolower($sortDirection) == 'asc' ? 'asc' : 'desc';

        // Column sorting
        $validSortColumns = ['order_no', 'vendor', 'status_invoice', 'status_delivered', 'ordered_at'];
        if (in_array($sortBy, $validSortColumns)) {
            $query->orderBy($sortBy, $sortDirection);
        } elseif ($sortBy === 'request_no') {
            $query->orderBy(
                PurchaseRequest::select('request_no')
                    ->whereColumn('purchase_requests.id', 'purchase_orders.request_id'),
                $sortDirection
            );
        } elseif ($sortBy === 'ordered_by') {
            $query->orderBy(
                Employee::select('name')
                    ->whereColumn('employees.id', 'purchase_orders.ordered_by'),
                $sortDirection
            );
        } else {
            $query->orderBy('created_at', $sortDirection);
        }

        $purchaseOrders = $query->paginate($perPage)->withQueryString();

        // Get employees for filter
        $employees = Employee::query()->select('id', 'name')->orderBy('name')->get();

        $canUpdate = Auth::user()->can('purchase_orders.edit');
        $canView = Auth::user()->can('purchase_orders.view');
        $canDelete = Auth::user()->can('purchase_orders.delete');
        $canCreate = Auth::user()->can('purchase_orders.create');

        return Inertia::render('Admin/PurchaseOrders/Index', [
            'purchaseOrders' => $purchaseOrders->through(function ($po) {
                return [
                    'id' => $po->id,
                    'order_no' => $po->order_no,
                    'pr_no' => $po->request?->request_no,
                    'vendor' => $po->vendor,
                    'status_invoice' => $po->status_invoice,
                    'status_delivered' => $po->status_delivered,
                    'ordered_by' => $po->applicant?->name,
                    'ordered_at' => $po->ordered_at->locale('id')->translatedFormat('d F Y'),
                    'branch' => $po->branch?->name ?? '-',
                ];
            }),
            'employees' => $employees,
            'filters' => $filters,
            'sortBy' => $sortBy,
            'sortDirection' => $sortDirection,
            'groupBy' => $groupBy,
            'canUpdate' => $canUpdate,
            'canView' => $canView,
            'canDelete' => $canDelete,
            'canCreate' => $canCreate,
        ]);
    }

    public function create()
    {
        abort_unless(Gate::allows('purchase_orders.create'), 403, 'Anda tidak memiliki akses untuk menambah pembelian');

        $orderNo = PurchaseOrder::generateOrderNo();

        $isSuperadmin = Auth::user()->hasRole("Superadmin");
        $userBranchId = Auth::user()->employee?->branch_id;
        $branches = Branch::where('id', $userBranchId)->pluck('id');
        $departmentIds = Department::where('branch_id', $userBranchId)->pluck('id');

        // Perbaiki referensi variabel $filters yang tak terdefinisi
        $items = Item::with(['unit', 'branch'])
            ->orderBy('name')
            ->when(!$isSuperadmin && $userBranchId != 2, function ($query) use ($branches) {
                return $query->whereIn('items.branch_id', $branches);
            })
            ->get();

        $purchaseRequests = PurchaseRequest::with(['items.item.unit'])
            ->where('status', 'approved')
            ->orderByDesc('request_no')
            ->when(!$isSuperadmin, function ($query) use ($departmentIds) {
                return $query->whereIn('purchase_requests.department_id', $departmentIds);
            })
            ->get()
            ->map(function ($pr) {
                return [
                    'id' => $pr->id,
                    'request_no' => $pr->request_no,
                    'department_id' => $pr->department_id,
                    'requirement' => $pr->requirement,
                    'items' => $pr->items->map(function ($item) {
                        return [
                            'id' => $item->id,
                            'item_id' => $item->item_id,
                            'code' => $item->item?->code,
                            'name' => $item->item?->name,
                            'stock' => $item->item?->stock,
                            'unit' => $item->item?->unit?->short_name,
                            'quantity_requested' => $item->quantity_approved ?? $item->quantity_requested,
                            'note' => $item->note,
                            'branch' => $item->item?->branch?->name,
                        ];
                    }),
                ];
            });

        $user = Auth::user()->load('employee.department.branch');
        $employees = Employee::query()->select('id', 'name')->orderBy('name')->get();

        $isSuperadmin = $user->hasRole('Superadmin');

        // Get branches - all for superadmin or branch_id 2, only user's branch for others
        $branches = ($isSuperadmin || $user->employee->branch_id == 2)
            ? Branch::query()->select('id', 'name')->orderBy('name')->get()
            : Branch::where('id', $user->employee->branch_id)->select('id', 'name')->get();

        return Inertia::render(
            'Admin/PurchaseOrders/Create',
            compact('orderNo', 'purchaseRequests', 'items', 'user', 'isSuperadmin', 'employees', 'branches')
        );
    }

    public function store(Request $request)
    {
        abort_unless(Gate::allows('purchase_orders.create'), 403, 'Anda tidak memiliki akses untuk menambah pembelian');

        $isSuperadmin = Auth::user()->hasRole("Superadmin");
        $userBranchId = Auth::user()->employee?->branch_id;

        $data = $request->validate([
            'request_id' => ['nullable', 'exists:purchase_requests,id'],
            'order_no' => ['nullable', 'string', 'unique:purchase_orders,order_no'],
            'ordered_at' => ['required', 'date'],
            'vendor' => ['required', 'string', 'max:255'],
            'note' => ['nullable', 'string', 'max:255'],
            'branch_id' => ['nullable', 'exists:branches,id'],
            'items' => ['required', 'array'],
            'items.*.item_id' => ['required_with:items', 'exists:items,id'],
            'items.*.quantity_ordered' => ['required_with:items', 'integer', 'min:1'],
            'items.*.cost' => ['nullable', 'numeric', 'min:0'],
            'items.*.note' => ['nullable', 'string', 'max:255'],
            'amount_paid' => ['nullable', 'numeric', 'min:0'],
        ]);

        // For non-superadmin (except branch_id 2), ensure branch_id matches user's branch
        if (!$isSuperadmin && $userBranchId && $userBranchId != 2 && $data['branch_id'] != $userBranchId) {
            return back()->withInput()->with('error', 'Anda tidak memiliki akses untuk membuat Purchase Order untuk cabang ini.');
        }

        try {
            DB::transaction(function () use ($data) {
                $amountPaid = $data['amount_paid'] ?? 0;

                $totalOrder = 0;
                foreach ($data['items'] as $item) {
                    $totalOrder += $item['quantity_ordered'] * ($item['cost'] ?? 0);
                }

                $statusInvoice = $totalOrder > $amountPaid ? PurchaseOrder::STATUS_INVOICE_BELUM_LUNAS : PurchaseOrder::STATUS_INVOICE_LUNAS;

                if ($amountPaid == 0 && $totalOrder > 0) {
                    $statusInvoice = PurchaseOrder::STATUS_INVOICE_BELUM_DIBAYAR;
                }

                $branchId = $data['branch_id'] ? $data['branch_id'] : Auth::user()->employee?->branch_id;

                $purchaseOrder = PurchaseOrder::create([
                    'request_id' => $data['request_id'],
                    'order_no' => $data['order_no'],
                    'ordered_at' => $data['ordered_at'],
                    'ordered_by' => Auth::user()->employee?->id ?? Auth::id(),
                    'status_invoice' => $statusInvoice,
                    'vendor' => $data['vendor'],
                    'note' => $data['note'] ?? null,
                    'branch_id' => $branchId,
                    'paid_amount' => $amountPaid,
                ]);

                foreach ($data['items'] as $item) {
                    $purchaseOrder->items()->create([
                        'item_id' => $item['item_id'],
                        'quantity_ordered' => $item['quantity_ordered'],
                        'cost' => $item['cost'] ?? 0,
                        'note' => $item['note'] ?? null,
                    ]);
                }

                app(CreatePurchaseOrderLogActivities::class)->execute($purchaseOrder->id, 'Membuat Purchase Order', 'Purchase Order baru dibuat dengan nomor ' . $purchaseOrder->order_no . ' untuk vendor ' . $purchaseOrder->vendor);

                if ($purchaseOrder->status_invoice != PurchaseOrder::STATUS_INVOICE_BELUM_DIBAYAR) {
                    app(CreatePurchaseOrderLogActivities::class)->execute($purchaseOrder->id, 'Update Pembayaran', 'Pembayaran sebesar ' . number_format($purchaseOrder->paid_amount, 0, ',', '.') . ' ditambahkan. Total pembayaran: ' . number_format($purchaseOrder->paid_amount, 0, ',', '.'));
                }
            });

            return redirect()->route('purchase-orders.index')->with('success', 'Purchase Order berhasil dibuat!');
        } catch (Exception $e) {
            Log::error('Failed to create purchase order', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'data' => $data
            ]);
            return back()->withInput()->with('error', 'Terjadi kesalahan saat menyimpan Purchase Order. Silakan coba lagi.');
        }
    }

    public function show(PurchaseOrder $purchaseOrder)
    {
        abort_unless(Gate::allows('purchase_orders.view'), 403, 'Anda tidak memiliki akses untuk melihat pembelian');

        // Check branch access for non-superadmin
        $isSuperadmin = Auth::user()->hasRole("Superadmin");
        $userBranchId = Auth::user()->employee?->branch_id;

        if (!$isSuperadmin && $userBranchId && $userBranchId != 2 && $purchaseOrder->branch_id != $userBranchId) {
            abort(403, 'Anda tidak memiliki akses untuk melihat Purchase Order dari cabang ini.');
        }

        $purchaseOrder->load(['request', 'applicant', 'branch', 'items.item.unit']);

        $total = $purchaseOrder->items->sum(function ($item) {
            return $item->quantity_ordered * ($item->cost ?? 0);
        });

        $activities = \App\Models\PurchaseOrderActivity::where('purchase_order_id', $purchaseOrder->id)
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

        return Inertia::render('Admin/PurchaseOrders/Show', [
            'po' => [
                'id' => $purchaseOrder->id,
                'number' => $purchaseOrder->order_no,
                'date' => $purchaseOrder->ordered_at->format('d/m/Y'),
                'vendor' => $purchaseOrder->vendor ? ['id' => $purchaseOrder->vendor, 'name' => $purchaseOrder->vendor] : null,
                'status_delivered' => $purchaseOrder->status_delivered,
                'status_invoice' => $purchaseOrder->status_invoice,
                'paid_amount' => $purchaseOrder->paid_amount,
                'total' => $total,
                'note' => $purchaseOrder->note,
                'ordered_by' => $purchaseOrder->applicant?->name,
                'branch' => $purchaseOrder->branch?->name ?? '-',
                'branch_id' => $purchaseOrder->branch_id,
                'from_pr' => $purchaseOrder->request ? ['id' => $purchaseOrder->request->id, 'number' => $purchaseOrder->request->request_no] : null,
                'items' => $purchaseOrder->items->map(function ($item) {
                    return [
                        'id' => $item->id,
                        'item_name' => $item->item?->name,
                        'item_code' => $item->item?->code,
                        'quantity_ordered' => $item->quantity_ordered,
                        'quantity_received' => $item->quantity_received ?? 0,
                        'cost' => $item->cost ?? 0,
                        'unit' => $item->item?->unit?->short_name,
                        'note' => $item->note,
                        'note_received' => $item->note_received,
                    ];
                }),
            ],
            'activities' => $activities,
        ]);
    }

    public function edit(PurchaseOrder $purchaseOrder)
    {
        abort_unless(Gate::allows('purchase_orders.edit'), 403, 'Anda tidak memiliki akses untuk mengubah pembelian');

        // Check branch access for non-superadmin
        $isSuperadmin = Auth::user()->hasRole("Superadmin");
        $userBranchId = Auth::user()->employee?->branch_id;

        if (!$isSuperadmin && $userBranchId && $userBranchId != 2 && $purchaseOrder->branch_id != $userBranchId) {
            abort(403, 'Anda tidak memiliki akses untuk mengubah Purchase Order dari cabang ini.');
        }

        $purchaseOrder->load(['items.item.unit']);

        $items = Item::with(['unit'])->orderBy('name')->get();
        $employees = Employee::query()->select('id', 'name')->orderBy('name')->get();

        // Get branches - all for superadmin or branch_id 2, only user's branch for others
        $branches = ($isSuperadmin || $userBranchId == 2)
            ? Branch::query()->select('id', 'name')->orderBy('name')->get()
            : Branch::where('id', $userBranchId)->select('id', 'name')->get();

        return Inertia::render('Admin/PurchaseOrders/Edit', [
            'po' => [
                'id' => $purchaseOrder->id,
                'number' => $purchaseOrder->order_no,
                'date' => $purchaseOrder->ordered_at->format('Y-m-d'),
                'request_no' => $purchaseOrder->order_no,
                'vendor' => $purchaseOrder->vendor,
                'note' => $purchaseOrder->note,
                'ordered_by' => $purchaseOrder->ordered_by,
                'branch_id' => $purchaseOrder->branch_id,
                'status_invoice' => $purchaseOrder->status_invoice,
                'status_delivered' => $purchaseOrder->status_delivered,
                'amount_paid' => $purchaseOrder->paid_amount,
                'items' => $purchaseOrder->items->map(function ($item) {
                    return [
                        'id' => $item->id,
                        'item_id' => $item->item_id,
                        'item_name' => $item->item?->name,
                        'item_code' => $item->item?->code,
                        'quantity_ordered' => $item->quantity_ordered,
                        'cost' => $item->cost ?? 0,
                        'unit' => $item->item?->unit?->short_name,
                        'note' => $item->note,
                    ];
                }),
            ],
            'items' => $items,
            'employees' => $employees,
            'branches' => $branches,
            'isSuperadmin' => $isSuperadmin,
        ]);
    }

    public function update(Request $request, PurchaseOrder $purchaseOrder)
    {
        abort_unless(Gate::allows('purchase_orders.edit'), 403, 'Anda tidak memiliki akses untuk mengubah pembelian');

        $isSuperadmin = Auth::user()->hasRole("Superadmin");
        $userBranchId = Auth::user()->employee?->branch_id;

        $data = $request->validate([
            'vendor' => ['required', 'string', 'max:255'],
            'ordered_at' => ['required', 'date'],
            'note' => ['nullable', 'string', 'max:255'],
            'branch_id' => ['nullable', 'exists:branches,id'],
            'items' => ['required', 'array'],
            'items.*.item_id' => ['required_with:items', 'exists:items,id'],
            'items.*.quantity_ordered' => ['required_with:items', 'integer', 'min:1'],
            'items.*.cost' => ['nullable', 'numeric', 'min:0'],
            'items.*.note' => ['nullable', 'string', 'max:255'],
            'amount_paid' => ['required', 'numeric', 'min:0'],
        ]);

        // For non-superadmin (except branch_id 2), ensure branch_id matches user's branch
        if (!$isSuperadmin && $userBranchId && $userBranchId != 2 && $data['branch_id'] != $userBranchId) {
            return back()->withInput()->with('error', 'Anda tidak memiliki akses untuk mengubah Purchase Order ke cabang ini.');
        }

        try {
            DB::transaction(function () use ($purchaseOrder, $data) {
                $purchaseOrder->update([
                    'vendor' => $data['vendor'],
                    'ordered_at' => $data['ordered_at'],
                    'note' => $data['note'] ?? null,
                    'branch_id' => $data['branch_id'],
                ]);

                $purchaseOrder->items()->delete();

                foreach ($data['items'] as $item) {
                    $purchaseOrder->items()->create([
                        'item_id' => $item['item_id'],
                        'quantity_ordered' => $item['quantity_ordered'],
                        'cost' => $item['cost'] ?? 0,
                        'note' => $item['note'] ?? null,
                    ]);
                }

                app(CreatePurchaseOrderLogActivities::class)->execute($purchaseOrder->id, 'Memperbarui Purchase Order', 'Purchase Order ' . $purchaseOrder->order_no . ' diperbarui');
            });

            return redirect()->route('purchase-orders.index')->with('success', 'Purchase Order berhasil diperbarui!');
        } catch (Exception $e) {
            Log::error('Failed to update purchase order', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'data' => $data
            ]);

            dd($e->getMessage());
            return back()->withInput()->with('error', 'Terjadi kesalahan saat memperbarui Purchase Order. Silakan coba lagi.');
        }
    }

    public function destroy(PurchaseOrder $purchaseOrder)
    {
        abort_unless(Gate::allows('purchase_orders.delete'), 403, 'Anda tidak memiliki akses untuk menghapus pembelian');

        try {
            DB::transaction(function () use ($purchaseOrder) {
                $purchaseOrder->items()->delete();
                $purchaseOrder->delete();
            });
            return redirect()->back()->with('success', 'Purchase Order berhasil dihapus');
        } catch (Throwable $e) {
            Log::error('Purchase Order destroy error', ['err' => $e->getMessage()]);
            return redirect()->back()->with('error', 'Gagal menghapus Purchase Order');
        }
    }

    public function print($id)
    {
        abort_unless(Gate::allows('purchase_orders.view'), 403, 'Anda tidak memiliki akses untuk melihat pembelian');

        return Inertia::render('Admin/PurchaseOrders/Print', [
            'purchaseOrder' => [
                'id' => $id,
                'number' => 'PO-' . now()->format('Y') . '-0001',
            ],
        ]);
    }

    public function printReceipt(PurchaseOrder $purchaseOrder)
    {
        abort_unless(Gate::allows('purchase_orders.view'), 403, 'Anda tidak memiliki akses untuk melihat pembelian');

        $purchaseOrder->load(['request', 'applicant', 'items.item.unit']);

        $total = $purchaseOrder->items->sum(function ($item) {
            return $item->quantity_ordered * ($item->cost ?? 0);
        });

        $data = [
            'po' => [
                'id' => $purchaseOrder->id,
                'number' => $purchaseOrder->order_no,
                'date' => $purchaseOrder->ordered_at->format('d/m/Y'),
                'vendor' => $purchaseOrder->vendor ? ['id' => $purchaseOrder->vendor, 'name' => $purchaseOrder->vendor] : null,
                'status_delivered' => $purchaseOrder->status_delivered,
                'status_invoice' => $purchaseOrder->status_invoice,
                'paid_amount' => $purchaseOrder->paid_amount,
                'total' => $total,
                'note' => $purchaseOrder->note,
                'receive_notes' => $purchaseOrder->receive_notes,
                'ordered_by' => $purchaseOrder->applicant?->name,
                'branch' => $purchaseOrder->applicant?->branch,
                'from_pr' => $purchaseOrder->request ? ['id' => $purchaseOrder->request->id, 'number' => $purchaseOrder->request->request_no] : null,
                'items' => $purchaseOrder->items->map(function ($item) {
                    return [
                        'id' => $item->id,
                        'item_name' => $item->item?->name,
                        'item_code' => $item->item?->code,
                        'quantity_ordered' => $item->quantity_ordered,
                        'quantity_received' => $item->quantity_received ?? 0,
                        'cost' => $item->cost ?? 0,
                        'unit' => $item->item?->unit?->short_name,
                        'note' => $item->note,
                        'note_received' => $item->note_received,
                    ];
                }),
            ],
        ];

        return view('purchase-orders.receipt', $data);
    }

    public function receive(Request $request, $id)
    {
        abort_unless(Gate::allows('purchase_orders.edit'), 403, 'Anda tidak memiliki akses untuk memproses penerimaan pembelian');

        $data = $request->validate([
            'quantities' => ['required', 'array'],
            'quantities.*.item_id' => ['required', 'exists:purchase_order_items,id'],
            'quantities.*.quantity_received' => ['required', 'integer', 'min:0'],
            'quantities.*.receive_note' => ['nullable', 'string', 'max:500'],
        ]);

        try {
            DB::transaction(function () use ($id, $data) {
                $purchaseOrder = PurchaseOrder::findOrFail($id);

                $statusDelivered = PurchaseOrder::STATUS_DELIVERED_PENDING;
                $totalReceived = 0;

                foreach ($data['quantities'] as $quantityData) {
                    $totalReceived += $quantityData['quantity_received'];
                    $purchaseOrder->items()
                        ->where('id', $quantityData['item_id'])
                        ->update([
                            'quantity_received' => $quantityData['quantity_received'],
                            'note_received' => $quantityData['receive_note'] ?? null
                        ]);
                }

                if ($totalReceived == $purchaseOrder->items->sum('quantity_ordered')) {
                    $statusDelivered = PurchaseOrder::STATUS_DELIVERED;
                } else {
                    $statusDelivered = PurchaseOrder::STATUS_DELIVERED_PARTIAL;
                }

                $purchaseOrder->update(['status_delivered' => $statusDelivered]);

                $data = [
                    'employee_id' => Auth::user()->employee?->id,
                    'order_id' => $purchaseOrder->id,
                    'source' => 'Pembelian',
                    'note' => 'Pembelian barang ' . $purchaseOrder->order_no . ' berhasil diterima oleh ' . Auth::user()->employee?->name,
                    'received_at' => now(),
                ];

                $data['items'] = $purchaseOrder->items->map(function ($item) use ($purchaseOrder) {
                    return [
                        'item_id' => $item->item_id,
                        'quantity_transferred' => $item->quantity_ordered,
                        'quantity_received' => $item->quantity_received,
                        'note' => $item->note_received,
                        'source' => PurchaseOrder::class,
                        'source_id' => $purchaseOrder->id,
                    ];
                });

                app(CreateGoodReceipt::class)->execute($data);

                $statusText = $statusDelivered === PurchaseOrder::STATUS_DELIVERED ? 'Barang Diterima Lengkap' : 'Barang Diterima Sebagian';
                app(CreatePurchaseOrderLogActivities::class)->execute($purchaseOrder->id, $statusText, 'Barang diterima dengan total ' . $totalReceived . ' item. Status: ' . $statusText);
            });

            Log::info('Purchase Order received successfully', ['po_id' => $id]);
            return back()->with('success', 'Barang berhasil diterima dan status Purchase Order diperbarui!');
        } catch (Exception $e) {
            dd($e->getMessage());
            Log::error('Failed to receive purchase order', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'po_id' => $id,
                'data' => $data
            ]);
            return back()->with('error', 'Terjadi kesalahan saat menyimpan data penerimaan barang. Silakan coba lagi.');
        }
    }

    public function updateReceive(Request $request, $id)
    {
        abort_unless(Gate::allows('purchase_orders.edit'), 403, 'Anda tidak memiliki akses untuk memproses penerimaan pembelian');

        $data = $request->validate([
            'quantities' => ['required', 'array'],
            'quantities.*.item_id' => ['required', 'exists:purchase_order_items,id'],
            'quantities.*.additional_quantity' => ['required', 'integer', 'min:0'],
            'quantities.*.receive_note' => ['nullable', 'string', 'max:500'],
        ]);

        try {
            DB::transaction(function () use ($id, $data) {
                $purchaseOrder = PurchaseOrder::findOrFail($id);

                $statusDelivered = PurchaseOrder::STATUS_DELIVERED_PENDING;
                $totalReceived = 0;

                foreach ($data['quantities'] as $quantityData) {
                    $currentReceived = $purchaseOrder->items()
                        ->where('id', $quantityData['item_id'])
                        ->value('quantity_received') ?? 0;

                    $newTotalReceived = $currentReceived + $quantityData['additional_quantity'];
                    $totalReceived += $newTotalReceived;

                    $purchaseOrder->items()
                        ->where('id', $quantityData['item_id'])
                        ->update([
                            'quantity_received' => $newTotalReceived,
                            'note_received' => $quantityData['receive_note'] ?? null
                        ]);
                }

                if ($totalReceived == $purchaseOrder->items->sum('quantity_ordered')) {
                    $statusDelivered = PurchaseOrder::STATUS_DELIVERED;
                } else {
                    $statusDelivered = PurchaseOrder::STATUS_DELIVERED_PARTIAL;
                }

                $purchaseOrder->update(['status_delivered' => $statusDelivered]);

                foreach ($data['quantities'] as $quantityData) {
                    if ($quantityData['additional_quantity'] > 0) {
                        $item = $purchaseOrder->items()->with('item')->where('id', $quantityData['item_id'])->first();

                        $stockInData = [
                            'type' => 'in',
                            'amount' => $quantityData['additional_quantity'],
                            'item_id' => $item->item_id,
                            'source_type' => PurchaseOrder::class,
                            'source_id' => $purchaseOrder->id,
                            'tanggal' => now()->toDateString(),
                            'note' => 'Pembelian barang ' . $purchaseOrder->order_no . ' berhasil diterima oleh ' . Auth::user()->employee?->name,
                        ];

                        app(UpdateStockItem::class)->execute($stockInData, $item->item);
                    }
                }

                $statusText = $statusDelivered === PurchaseOrder::STATUS_DELIVERED ? 'Barang Diterima Lengkap' : 'Update Penerimaan Barang';
                app(CreatePurchaseOrderLogActivities::class)->execute($purchaseOrder->id, $statusText, 'Update penerimaan barang dengan total ' . $totalReceived . ' item. Status: ' . $statusText);


                //update penerimaan barang good receipt
                $purchaseOrder->items->each(function ($item) use ($purchaseOrder) {
                    $goodReceipt = GoodReceipt::where('order_id', $purchaseOrder->id)->first();

                    $itemGoodReceipt = $goodReceipt->items()->where('item_id', $item->item_id)->first();

                    if ($itemGoodReceipt) {
                        $itemGoodReceipt->update([
                            'quantity_received' => $item->quantity_received,
                            'note_received' => $item->note_received,
                        ]);
                    }
                });
            });

            Log::info('Purchase Order receive updated successfully', ['po_id' => $id]);
            return back()->with('success', 'Update penerimaan barang berhasil dan status Purchase Order diperbarui!');
        } catch (Exception $e) {
            Log::error('Failed to update receive purchase order', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'po_id' => $id,
                'data' => $data
            ]);
            return back()->with('error', 'Terjadi kesalahan saat menyimpan data update penerimaan barang. Silakan coba lagi.');
        }
    }

    public function updatePayment(Request $request, $id)
    {
        abort_unless(Gate::allows('purchase_orders.edit'), 403, 'Anda tidak memiliki akses untuk memperbarui pembayaran pembelian');

        $data = $request->validate([
            'additional_amount' => ['required', 'numeric', 'min:0'],
        ]);

        try {
            DB::transaction(function () use ($id, $data) {
                $purchaseOrder = PurchaseOrder::findOrFail($id);

                $newPaidAmount = $purchaseOrder->paid_amount + $data['additional_amount'];

                $totalOrder = $purchaseOrder->items->sum(function ($item) {
                    return $item->quantity_ordered * $item->cost;
                });

                $statusInvoice = $totalOrder > $newPaidAmount ? PurchaseOrder::STATUS_INVOICE_BELUM_LUNAS : PurchaseOrder::STATUS_INVOICE_LUNAS;

                if ($newPaidAmount == 0 && $totalOrder > 0) {
                    $statusInvoice = PurchaseOrder::STATUS_INVOICE_BELUM_DIBAYAR;
                }

                $purchaseOrder->update([
                    'paid_amount' => $newPaidAmount,
                    'status_invoice' => $statusInvoice,
                ]);

                app(CreatePurchaseOrderLogActivities::class)->execute(
                    $purchaseOrder->id,
                    'Update Pembayaran',
                    'Pembayaran sebesar ' . number_format($data['additional_amount'], 0, ',', '.') . ' ditambahkan. Total pembayaran: ' . number_format($newPaidAmount, 0, ',', '.')
                );
            });

            Log::info('Purchase Order payment updated successfully');
            return redirect()->back()->with('success', 'Pembayaran berhasil diperbarui!');
        } catch (Exception $e) {
            dd($e->getMessage());
            Log::error('Error updating Purchase Order payment: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Terjadi kesalahan saat memperbarui pembayaran: ' . $e->getMessage());
        }
    }
}
