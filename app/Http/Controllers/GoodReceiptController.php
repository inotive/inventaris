<?php

namespace App\Http\Controllers;

use App\Actions\Data\GoodReceipt\CreateGoodReceipt;
use App\Actions\Data\GoodTransfer\ReceiveGoodTranfer;
use App\Actions\Data\Item\UpdateStockItem;
use App\Actions\Data\PurchaseOrder\CreatePurchaseOrderLogActivities;
use App\Http\Requests\GoodReceipt\CreateGoodReceiptRequest;
use Inertia\Inertia;
use App\Models\Branch;
use App\Models\Department;
use App\Models\Employee;
use App\Models\GoodReceipt;
use App\Models\GoodTransfer;
use App\Models\Item;
use Illuminate\Http\Request;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderActivity;
use Illuminate\Support\Facades\Auth;

class GoodReceiptController extends Controller
{
    public function index(Request $request)
    {
        $perPage = max(1, min((int) $request->get('per_page', 10), 100));

        $filters = [
            'q'          => $request->string('q')->toString(),
            'order_id'   => $request->integer('order_id') ?: null,
            'transfer_id' => $request->integer('transfer_id') ?: null,
            'branch_id'  => $request->integer('branch_id') ?: null,
            'date_from'  => $request->string('date_from')->toString(),
            'date_to'    => $request->string('date_to')->toString(),
            'employee_id' => $request->integer('employee_id') ?: null,
        ];

        $sortBy        = $request->get('sortBy', 'created_at');
        $sortDirection = strtolower($request->get('sortDirection', 'desc')) === 'asc' ? 'asc' : 'desc';
        $groupBy       = $request->get('groupBy');

        $user = Auth::user();
        $isSuperadmin = $user->hasRole('Superadmin');
        $branchId = $user->employee->branch_id;
        $departmentIds = Department::where('branch_id', $branchId)->pluck('id');

        // Query builder
        $query = GoodReceipt::query()
            ->with([
                'receiver:id,name',
                'order:id,order_no,vendor',
                'transfer',
                'transfer.fromBranch:id,name',
                'transfer.toBranch:id,name'
            ])
            ->when(
                !$isSuperadmin && $branchId != 2,
                function ($q) use ($branchId) {
                    $q->where(function ($subQ) use ($branchId) {
                        $subQ->whereHas('order.applicant', function ($qr) use ($branchId) {
                            $qr->where('branch_id', $branchId);
                        })
                            ->orWhereHas('transfer', function ($qr) use ($branchId) {
                                $qr->where('to_branch', $branchId);
                            });
                    });
                }
            )
            ->when($filters['q'], function ($q) use ($filters) {
                $search = $filters['q'];
                $q->where(function ($sq) use ($search) {
                    $sq->where('source', 'like', "%{$search}%")
                        ->orWhereHas('order', function ($orderQ) use ($search) {
                            $orderQ->where('order_no', 'like', "%{$search}%")
                                ->orWhere('vendor', 'like', "%{$search}%");
                        })
                        ->orWhereHas('transfer', function ($transferQ) use ($search) {
                            $transferQ->where('transfer_no', 'like', "%{$search}%");
                        });
                });
            })
            ->when($filters['transfer_id'],  fn($q) => $q->where('transfer_id', $filters['transfer_id']))
            ->when($filters['branch_id'],    fn($q) => $q->whereHas('transfer', function ($qr) use ($filters) {
                $qr->where('from_branch', $filters['branch_id']);
            }))
            ->when($filters['date_from'],    fn($q) => $q->whereDate('received_at', '>=', $filters['date_from']))
            ->when($filters['date_to'],      fn($q) => $q->whereDate('received_at', '<=', $filters['date_to']))
            ->when($filters['employee_id'],  fn($q) => $q->where('employee_id', $filters['employee_id']));

        // Sorting
        // For groupBy 'employee_id', order by receiver's name
        if ($groupBy === 'employee_id') {
            $query->orderBy(
                Employee::select('name')
                    ->whereColumn('employees.id', 'good_receipts.employee_id')
            );
        }

        // Sorting and grouping
        switch ($sortBy) {
            case 'vendor':
            case 'created_at':
                $query->orderBy($sortBy, $sortDirection);
                break;
            case 'order_id':
                $query->orderBy(
                    PurchaseOrder::select('order_no')
                        ->whereColumn('purchase_orders.id', 'good_receipts.order_id'),
                    $sortDirection
                );
                break;
            case 'branch_id':
                $query->orderBy(
                    Branch::select('name')
                        ->whereColumn('branches.id', 'good_receipts.branch_id'),
                    $sortDirection
                );
                break;
            case 'employee_id':
                $query->orderBy(
                    Employee::select('name')
                        ->whereColumn('employees.id', 'good_receipts.employee_id'),
                    $sortDirection
                );
                break;
            default:
                $query->orderBy('created_at', $sortDirection);
                break;
        }

        $goodReceipts = $query->paginate($perPage)->withQueryString();

        $canEdit = Auth::user()->can('good_receipts.edit');
        $canDelete = Auth::user()->can('good_receipts.delete');
        $canView = Auth::user()->can('good_receipts.view');
        $canCreate = Auth::user()->can('good_receipts.create');

        $employees = Employee::query()->select('id', 'name')->when(!$isSuperadmin && $branchId != 2, function ($query) use ($departmentIds) {
            return $query->whereIn('department_id', $departmentIds);
        })->orderBy('name')->get();

        return Inertia::render('Admin/GoodReceipts/Index', [
            'can_edit' => $canEdit,
            'can_delete' => $canDelete,
            'can_view' => $canView,
            'can_create' => $canCreate,
            'goodReceipts' => $goodReceipts->through(function ($gr) {
                return [
                    'id' => $gr->id,
                    'source' => $gr->source,
                    'employee' => $gr->receiver?->name,
                    'order_no' => $gr->order?->order_no,
                    'vendor' => $gr->order?->vendor,
                    'transfer_no' => $gr->transfer?->transfer_no,
                    'from_branch' => $gr->transfer?->fromBranch?->name,
                    'received_at' => $gr->received_at->locale('id')->translatedFormat('d F Y'),
                ];
            }),
            'branches' => Branch::query()->select('id', 'name')->orderBy('name')->get(),
            'employees' => $employees,
            'filters' => array_merge($filters),
            'sortBy' => $sortBy,
            'sortDirection' => $sortDirection,
            'groupBy' => in_array($groupBy, ['department', 'ordered_by'], true) ? $groupBy : null,
            // Sidebar counters
            'sidebarCounts' => [
                'total' => GoodReceipt::count(),
            ],
        ]);
    }

    public function create()
    {
        $departments = Department::query()->select('id', 'name')->orderBy('name')->get();

        if (Auth::user()->hasRole('Superadmin')) {
            $employees = Employee::query()->select('id', 'name')->orderBy('name')->get();
        } else {
            $employees = Auth::user()->employee()->get();
        }
        $isSuperadmin = Auth::user()->hasRole('Superadmin');
        $departementIds = Department::query()->select('id')->orderBy('name')->get()->pluck('id');

        $order = PurchaseOrder::query()
            ->with(['applicant:id,name'])
            ->select('id', 'order_no', 'vendor', 'ordered_by')
            ->where('status_delivered', 'pending')
            ->when(!$isSuperadmin && Auth::user()->employee->branch_id != 2, function ($query) use ($departementIds) {
                return $query->where('branch_id', Auth::user()->employee->branch_id);
            })
            ->orderBy('order_no')
            ->get();

        $transfer = GoodTransfer::query()
            ->with(['fromBranch', 'toBranch', 'sentBy:id,name'])
            ->select('id', 'transfer_no', 'from_branch', 'to_branch', 'sent_by')
            ->where('status', 'Shipped')
            ->when(!$isSuperadmin && Auth::user()->employee->branch_id != 2, function ($query) use ($departementIds) {
                return $query->where('to_branch', Auth::user()->employee->branch_id);
            })
            ->orderBy('transfer_no')
            ->get()
            ->map(function ($t) {
                return [
                    'id' => $t->id,
                    'transfer_no' => $t->transfer_no,
                    'from_branch' => $t->fromBranch?->name,
                    'to_branch' => $t->toBranch?->name,
                    'sent_by' => $t->sent_by,
                    'sentBy' => $t->sentBy,
                ];
            });

        // Load all items for the multiselect
        $items = \App\Models\Item::with('unit')->select('id', 'name', 'code', 'unit_id')->get();

        return Inertia::render('Admin/GoodReceipts/Create', [
            'departments' => $departments,
            'employees' => $employees,
            'order' => $order,
            'transfer' => $transfer,
            'items' => $items,
            'isSuperadmin' => $isSuperadmin,
            'user' => Auth::user()->load('employee'),
        ]);
    }

    public function loadItems(Request $request, $id)
    {
        $isSuperadmin = Auth::user()->hasRole('Superadmin');
        if ($request->type === 'pembelian') {

            $order = PurchaseOrder::with(['items.item.unit'])
                ->where('status_delivered', 'pending')
                ->when(!$isSuperadmin && Auth::user()->employee->branch_id != 2, function ($query) {
                    return $query->where('branch_id', Auth::user()->employee->branch_id);
                })
                ->find($id);

            if (!$order) {
                return response()->json(['error' => 'Purchase order not found'], 404);
            }


            $items = $order->items->map(function ($item) {
                return [
                    'id' => $item->id,
                    'item_id' => $item->item_id,
                    'name' => $item->item->name,
                    'code' => $item->item->code,
                    'unit' => $item->item->unit,
                    'quantity_requested' => $item->quantity_ordered,
                    'note' => $item->note,
                ];
            });
        } else {
            $transfer = GoodTransfer::with(['items.item.unit'])->where('status', 'Shipped')->find($id);

            if (!$transfer) {
                return response()->json(['error' => 'Good transfer not found'], 404);
            }

            $items = $transfer->items->map(function ($item) use ($isSuperadmin) {
                $itemId = $item->item_id;

                if (!$isSuperadmin) {
                    $itemId = Item::where('code', $item->item->code)->where('branch_id', Auth::user()->employee->branch_id)->first()->id;
                }

                return [
                    'id' => $item->id,
                    'item_id' => $itemId,
                    'name' => $item->item->name,
                    'code' => $item->item->code,
                    'unit' => $item->item->unit,
                    'quantity_requested' => $item->quantity_transferred,
                    'note' => $item->note,
                ];
            });
        }

        return response()->json($items);
    }

    public function store(CreateGoodReceiptRequest $request)
    {
        $items = collect($request->items)->map(function ($item) {
            $item['note'] = $item['received_note'];
            $item['quantity_transferred'] = $item['quantity_received'];
            $item['quantity_received'] = $item['quantity_actually_received'];
            return $item;
        })->toArray();

        $request->merge(['items' => $items]);


        if ($request->source === 'Pembelian') {
            $order = PurchaseOrder::find($request->order_id);

            $statusDelivered = PurchaseOrder::STATUS_DELIVERED_PENDING;
            $totalReceived = 0;

            foreach ($request->items as $quantityData) {
                $totalReceived += $quantityData['quantity_received'];
                $order->items()
                    ->where('item_id', $quantityData['item_id'])
                    ->update([
                        'quantity_received' => $quantityData['quantity_received'],
                        'note_received' => $quantityData['note'] ?? null
                    ]);
            }

            if ($totalReceived == $order->items->sum('quantity_ordered')) {
                $statusDelivered = PurchaseOrder::STATUS_DELIVERED;
            } else {
                $statusDelivered = PurchaseOrder::STATUS_DELIVERED_PARTIAL;
            }

            $order->update([
                'status_delivered' => $statusDelivered,
            ]);

            $statusMessage = $statusDelivered === PurchaseOrder::STATUS_DELIVERED ? 'Barang Diterima Lengkap' : 'Barang Diterima Sebagian';
            app(CreatePurchaseOrderLogActivities::class)->execute($order->id, 'Barang Diterima', 'Barang diterima dengan total ' . $totalReceived . ' item. Status: ' . $statusMessage);
            app(CreateGoodReceipt::class)->execute($request->all());
        } else {
            $transfer = GoodTransfer::with('items')->find($request->transfer_id);

            $dataItem = collect($items);

            $items = $transfer->items->map(function ($item) use ($dataItem, $transfer) {
                $toBranchItem = $transfer->toBranch->items()->where('code', $item->item->code)->first();

                $data = $dataItem->where('item_id', $toBranchItem->id)->first();

                return [
                    'item_id' => $item->id,
                    'quantity_transferred' => $data['quantity_transferred'],
                    'quantity_received' => $data['quantity_received'],
                    'receive_note' => $data['note'],
                ];
            });

            $request = ['quantities' => $items];

            app(ReceiveGoodTranfer::class)->execute($transfer, $request);
        }

        return redirect()->route('good-receipts.index');
    }

    public function update(CreateGoodReceiptRequest $request, $id)
    {
        $goodReceipt = GoodReceipt::findOrFail($id);

        $goodReceipt->update($request->only([
            'employee_id',
            'order_id',
            'transfer_id',
            'source',
            'note',
            'received_at'
        ]));

        // Delete existing items and create new ones
        $goodReceipt->items()->delete();
        $goodReceipt->items()->createMany($request->items);

        // Update stock
        $goodReceipt->items->each(function ($item) {
            $item->item->stock->update([
                'last_stock' => $item->item->stock->last_stock + $item->quantity_received,
            ]);
        });

        return redirect()->route('good-receipts.index');
    }

    public function show($id)
    {
        $goodReceipt = GoodReceipt::with([
            'receiver:id,name',
            'order:id,order_no,vendor',
            'order.items:id,purchase_order_id,item_id,quantity_ordered,quantity_received',
            'order.items.item:id,code,name',
            'transfer.fromBranch:id,name',
            'transfer.toBranch:id,name',
            'items.item.unit',
            'activities.createdBy'
        ])->findOrFail($id);

        $departments = Department::query()->select('id', 'name')->orderBy('name')->get();

        if (Auth::user()->hasRole('Superadmin')) {
            $employees = Employee::query()->select('id', 'name')->orderBy('name')->get();
        } else {
            $employees = Auth::user()->employee()->get();
        }

        // Fetch activities from GoodReceiptActivity
        $activities = $goodReceipt->activities->map(function ($activity) {
            return [
                'id' => $activity->id,
                'title' => $activity->title,
                'description' => $activity->description,
                'time' => $activity->created_at->locale('id')->translatedFormat('d F Y H:i'),
                'user' => $activity->createdBy?->name ?? 'System',
            ];
        });

        return Inertia::render('Admin/GoodReceipts/Show', [
            'goodReceipt' => $goodReceipt,
            'departments' => $departments,
            'employees' => $employees,
            'activities' => $activities,
        ]);
    }

    public function edit($id)
    {
        $goodReceipt = GoodReceipt::with([
            'receiver:id,name',
            'order:id,order_no,vendor',
            'transfer.fromBranch:id,name',
            'transfer.toBranch:id,name',
            'items.item.unit'
        ])->findOrFail($id);

        $departments = Department::query()->select('id', 'name')->orderBy('name')->get();

        $employees = Employee::query()->select('id', 'name')->orderBy('name')->get();

        $isSuperadmin = Auth::user()->hasRole('Superadmin');
        $departementIds = Department::query()->select('id')->orderBy('name')->get()->pluck('id');

        $order = PurchaseOrder::query()
            ->with(['applicant:id,name'])
            ->select('id', 'order_no', 'vendor', 'ordered_by')
            ->where('status_delivered', 'pending')
            ->when(!$isSuperadmin, function ($query) use ($departementIds) {
                return $query->where('branch_id', Auth::user()->employee->branch_id);
            })
            ->orderBy('order_no')
            ->get();

        $transfer = GoodTransfer::query()
            ->with(['fromBranch', 'toBranch', 'sentBy:id,name'])
            ->select('id', 'transfer_no', 'from_branch', 'to_branch', 'sent_by')
            ->where('status', 'Shipped')
            ->when(!$isSuperadmin, function ($query) use ($departementIds) {
                return $query->where('to_branch', Auth::user()->employee->branch_id);
            })
            ->orderBy('transfer_no')
            ->get()
            ->map(function ($t) {
                return [
                    'id' => $t->id,
                    'transfer_no' => $t->transfer_no,
                    'from_branch' => $t->fromBranch?->name,
                    'to_branch' => $t->toBranch?->name,
                    'sent_by' => $t->sent_by,
                    'sentBy' => $t->sentBy,
                ];
            });

        // Load all items for the multiselect
        $items = \App\Models\Item::with('unit')->select('id', 'name', 'code', 'unit_id')->get();

        $isSuperadmin = Auth::user()->hasRole('Superadmin');

        return Inertia::render('Admin/GoodReceipts/Edit', [
            'goodReceipt' => $goodReceipt,
            'departments' => $departments,
            'employees' => $employees,
            'order' => $order,
            'transfer' => $transfer,
            'items' => $items,
            'isSuperadmin' => $isSuperadmin,
        ]);
    }

    public function print($id)
    {
        return Inertia::render('Admin/GoodReceipts/Print', [
            'receive' => [
                'id' => $id,
                'number' => 'GR-' . now()->format('Y') . '-0001',
            ],
        ]);
    }

    public function destroy($id)
    {
        $goodReceipt = GoodReceipt::findOrFail($id);
        $goodReceipt->delete();
        return redirect()->route('good-receipts.index');
    }
}
