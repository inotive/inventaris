<?php

namespace App\Http\Controllers;

use App\Actions\Data\GoodTransfer\CreateGoodTransferLogActivities;
use App\Actions\Data\GoodTransfer\ReceiveGoodTranfer;
use App\Actions\Data\Item\UpdateStockItem;
use Exception;
use App\Models\Item;
use Inertia\Inertia;
use App\Models\Branch;
use App\Models\Employee;
use App\Models\ItemStock;
use App\Models\GoodTransfer;
use Illuminate\Http\Request;
use App\Models\PurchaseRequest;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate; // ⬅️ tambahkan
use Spatie\Permission\Traits\HasRoles;

class GoodTransferController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        abort_unless(Gate::allows('good_transfers.view'), 403, 'Anda tidak memiliki akses untuk melihat perpindahan barang');

        $perPage = max(1, min((int) $request->get('per_page', 10), 100));
        $filters = [
            'q' => $request->string('q')->toString(),
            'from_branch' => $request->integer('from_branch') ?: null,
            'to_branch' => $request->integer('to_branch') ?: null,
            'date_from' => $request->string('date_from')->toString(),
            'date_to' => $request->string('date_to')->toString(),
            'status' => $request->string('status')->toString(),
        ];

        $sortBy = $request->get('sortBy', 'created_at');
        $sortDirection = $request->get('sortDirection', 'desc');
        $groupBy = $request->get('groupBy');

        $isSuperadmin = Auth::user()->hasRole("Superadmin");
        $branchScopeIds = Branch::where('id', Auth::user()->employee->branch_id)->pluck('id'); // ⬅️ rename untuk mencegah tabrakan nama

        $query = GoodTransfer::query()
            ->with(['fromBranch:id,name', 'toBranch:id,name', 'sentBy:id,name', 'receivedBy:id,name'])
            ->when(!$isSuperadmin, function ($query) use ($branchScopeIds) {
                return $query->whereIn('from_branch', $branchScopeIds)->orWhereIn('to_branch', $branchScopeIds);
            });

        // Apply filters
        if ($filters['q']) {
            $query->where(function ($q) use ($filters) {
                $search = $filters['q'];
                $q->where('transfer_no', 'like', "%{$search}%");
            });
        }

        // Other filters
        if ($filters['from_branch']) $query->where('from_branch', $filters['from_branch']);
        if ($filters['to_branch']) $query->where('to_branch', $filters['to_branch']);
        if ($filters['date_from']) $query->whereDate('transferred_at', '>=', $filters['date_from']);
        if ($filters['date_to']) $query->whereDate('transferred_at', '<=', $filters['date_to']);
        if ($filters['status']) $query->where('status', $filters['status']);

        // Sorting
        $sortDirection = strtolower($sortDirection) === 'asc' ? 'asc' : 'desc';
        $validSortColumns = ['transfer_no', 'from_branch', 'to_branch', 'transferred_at', 'status', 'created_at'];
        $query->orderBy(in_array($sortBy, $validSortColumns) ? $sortBy : 'created_at', $sortDirection);

        // Dropdown cabang (dibatasi jika bukan superadmin)
        $branches = Branch::when(!$isSuperadmin, function ($q) use ($branchScopeIds) {
            return $q->whereIn('id', $branchScopeIds);
        })->select('id', 'name')->orderBy('name')->get();

        $goodTransfers = $query->paginate($perPage)->withQueryString();

        return Inertia::render('Admin/GoodTransfers/Index', [
            'goodTransfers' => $goodTransfers->through(function ($gt) {
                return [
                    'id' => $gt->id,
                    'transfer_no' => $gt->transfer_no,
                    'from_branch' => $gt->fromBranch?->name,
                    'to_branch' => $gt->toBranch?->name,
                    'vendor' => $gt->vendor,
                    'status' => $gt->status,
                    'sent_by' => $gt->sentBy?->name,
                    'received_by' => $gt->receivedBy?->name,
                    'transferred_at' => $gt->transferred_at->locale('id')->translatedFormat('d F Y'),
                    'created_at' => Carbon::parse($gt->created_at)->locale('id')->translatedFormat('d F Y'),
                ];
            }),
            'branches' => $branches,
            'branch_to' => Branch::select('id', 'name')->orderBy('name')->get(),
            'employees' => Employee::select('id', 'name')->orderBy('name')->get(),
            'filters' => array_merge($filters),
            'sortBy' => $sortBy,
            'sortDirection' => $sortDirection,
            'groupBy' => in_array($groupBy, ['transferred_at', 'status', 'from_branch', 'to_branch'], true) ? $groupBy : null,
            'sidebarCounts' => [
                'total' => GoodTransfer::count(),
            ],
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        abort_unless(Gate::allows('good_transfers.create'), 403, 'Anda tidak memiliki akses untuk menambah perpindahan barang');

        $user = Auth::user()->load('employee.branch');

        $transfer_no = GoodTransfer::generateTransferNo();
        $branches = Branch::orderBy('id')->get();
        $employees = Employee::orderBy('name')->get();
        $isSuperadmin = Auth::user()->hasRole("Superadmin");
        $branchIds = Branch::where('id', Auth::user()->employee->branch_id)->pluck('id');

        // fix: hilangkan referensi $filters tak terdefinisi
        $items = Item::with(['unit', 'stock:id,item_id,last_stock'])
            ->orderBy('name')
            ->when(!$isSuperadmin, function ($query) use ($branchIds) {
                return $query->whereIn('items.branch_id', $branchIds);
            })->get();

        $roles = Auth::user()->getRoleNames()->first();

        return Inertia::render(
            'Admin/GoodTransfers/Create',
            compact('transfer_no',  'branches', 'employees', 'items', 'user', 'roles')
        );
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        abort_unless(Gate::allows('good_transfers.create'), 403, 'Anda tidak memiliki akses untuk menambah perpindahan barang');

        $data = $request->validate([
            'from_branch_id' => ['required', 'exists:branches,id'],
            'to_branch_id' => ['required', 'exists:branches,id'],
            'employee_id' => ['required', 'exists:employees,id'],
            'transferred_at' => ['required', 'date'],
            'purpose' => ['nullable', 'string'],
            'items' => ['required', 'array'],
            'items.*.item_id' => ['required_with:items', 'exists:items,id'],
            'items.*.quantity_transferred' => ['required_with:items', 'integer', 'min:1'],
            'items.*.note' => ['nullable', 'string', 'max:225'],
        ]);

        try {
            DB::transaction(function () use ($data) {

                $goodTransfer = GoodTransfer::create([
                    'from_branch' => $data['from_branch_id'],
                    'to_branch' => $data['to_branch_id'],
                    'sent_by' => $data['employee_id'],
                    'transferred_at' => $data['transferred_at'],
                    'purpose' => $data['purpose'] ?? null,
                    'status' => 'Shipped',
                ]);

                if ($data['items']) {
                    $goodTransfer->items()->createMany($data['items']);

                    //create item stock out
                    foreach ($data['items'] as $quantity) {
                        $stockOutData = [
                            'type' => 'out',
                            'amount' => $quantity['quantity_transferred'],
                            'item_id' => $quantity['item_id'],
                            'source_type' => GoodTransfer::class,
                            'source_id' => $goodTransfer->id,
                            'tanggal' => now()->toDateString(),
                            'note' => $quantity['note'] ? $quantity['note'] : 'Pemindahan barang ' . $goodTransfer->transfer_no . ' berhasil dibuat dan dikirim dari ' . $goodTransfer->fromBranch->name . ' ke ' . $goodTransfer->toBranch->name,
                        ];

                        app(UpdateStockItem::class)->execute($stockOutData, Item::find($quantity['item_id']));
                    }
                }

                app(CreateGoodTransferLogActivities::class)->execute(
                    $goodTransfer->id,
                    'Pemindahan Barang Dibuat',
                    'Pemindahan barang ' . $goodTransfer->transfer_no . ' berhasil dibuat dan dikirim dari ' . $goodTransfer->fromBranch->name . ' ke ' . $goodTransfer->toBranch->name
                );
            });

            return redirect()->route('good-transfers.index')->with('success', 'Pemindahan barang berhasil dibuat!');
        } catch (Exception $e) {
            Log::error('Terjadi kesalahan saat menyimpan pemindahan barang', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'data' => $data
            ]);
            return back()->withInput()->with('error', 'Terjadi kesalahan saat menyimpan pemindahan barang. Silakan coba lagi.' . $e->getMessage());
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(GoodTransfer $goodTransfer)
    {
        abort_unless(Gate::allows('good_transfers.view'), 403, 'Anda tidak memiliki akses untuk melihat detail perpindahan barang');

        $goodTransfer->load(['fromBranch', 'toBranch', 'sentBy', 'receivedBy', 'items.item.unit', 'activities.createdBy']);
        $user = Auth::user()->load('employee', 'roles');

        return Inertia::render('Admin/GoodTransfers/Show', [
            'isSuperadmin' => Auth::user()->hasRole("Superadmin"),
            'goodTransfer' => [
                'id' => $goodTransfer->id,
                'transfer_no' => $goodTransfer->transfer_no,
                'from_branch' => $goodTransfer->fromBranch?->name,
                'from_branch_id' => $goodTransfer->from_branch,
                'to_branch' => $goodTransfer->toBranch?->name,
                'to_branch_id' => $goodTransfer->to_branch,
                'sent_by' => $goodTransfer->sentBy?->name,
                'sent_by_id' => $goodTransfer->sent_by,
                'received_by' => $goodTransfer->receivedBy?->name,
                'purpose' => $goodTransfer->purpose,
                'status' => $goodTransfer->status,
                'transferred_at' => $goodTransfer->transferred_at->locale('id')->translatedFormat('d F Y'),
                'created_at' => $goodTransfer->created_at->locale('id')->translatedFormat('d F Y H:i'),
                'updated_at' => $goodTransfer->updated_at->locale('id')->translatedFormat('d F Y H:i'),
            ],
            'items' => $goodTransfer->items->map(function ($item) {
                return [
                    'id' => $item->id,
                    'item_name' => $item->item?->name,
                    'item_code' => $item->item?->code,
                    'quantity_transferred' => $item->quantity_transferred,
                    'quantity_received' => $item->quantity_received,
                    'unit' => $item->item?->unit?->short_name,
                    'note' => $item->note,
                    'note_received' => $item->note_received,
                ];
            }),
            'activities' => $goodTransfer->activities->map(function ($activity) {
                return [
                    'id' => $activity->id,
                    'title' => $activity->title,
                    'description' => $activity->description,
                    'time' => $activity->created_at->locale('id')->translatedFormat('d F Y H:i'),
                    'created_by' => $activity->createdBy?->name,
                ];
            }),
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'employee' => $user->employee ? [
                    'id' => $user->employee->id,
                    'name' => $user->employee->name,
                    'branch_id' => $user->employee->branch_id,
                ] : null,
                'roles' => $user->roles->pluck('name')->toArray(),
            ],
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(GoodTransfer $goodTransfer)
    {
        abort_unless(Gate::allows('good_transfers.edit'), 403, 'Anda tidak memiliki akses untuk mengubah perpindahan barang');

        // Prevent editing if status is Received or Canceled
        if (in_array($goodTransfer->status, ['Received', 'Canceled'])) {
            return redirect()->route('good-transfers.index')
                ->with('error', 'Pemindahan barang dengan status ' . $goodTransfer->status . ' tidak dapat diubah.');
        }

        $user = Auth::user()->load('employee.branch');

        $goodTransfer->load(['items.item.unit', 'sentBy', 'receivedBy']);
        $branches = Branch::orderBy('id')->get();
        $employees = Employee::orderBy('name')->get();
        $items = Item::with(['unit', 'stock:id,item_id,last_stock'])->orderBy('name')->get();
        $roles = Auth::user()->getRoleNames()->first();

        return Inertia::render('Admin/GoodTransfers/Edit', [
            'goodTransfer' => [
                'id' => $goodTransfer->id,
                'transfer_no' => $goodTransfer->transfer_no,
                'from_branch_id' => $goodTransfer->from_branch,
                'to_branch_id' => $goodTransfer->to_branch,
                'employee_id' => $goodTransfer->sent_by,
                'transferred_at' => $goodTransfer->transferred_at->format('Y-m-d'),
                'purpose' => $goodTransfer->purpose,
                'status' => $goodTransfer->status,
            ],
            'items' => $goodTransfer->items->map(function ($item) {
                return [
                    'id' => $item->id,
                    'item_id' => $item->item_id,
                    'amount' => $item->quantity_transferred,
                    'note' => $item->note,
                    'item_name' => $item->item?->name,
                    'item_code' => $item->item?->code,
                    'unit' => $item->item?->unit,
                ];
            }),
            'branches' => $branches,
            'employees' => $employees,
            'allItems' => $items,
            'user' => $user,
            'roles' => $roles,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, GoodTransfer $goodTransfer)
    {
        abort_unless(Gate::allows('good_transfers.edit'), 403, 'Anda tidak memiliki akses untuk mengubah perpindahan barang');

        // Prevent updating if status is Received or Canceled
        if (in_array($goodTransfer->status, ['Received', 'Canceled'])) {
            return back()->with('error', 'Pemindahan barang dengan status ' . $goodTransfer->status . ' tidak dapat diubah.');
        }

        $data = $request->validate([
            'from_branch_id' => ['required', 'exists:branches,id'],
            'to_branch_id' => ['required', 'exists:branches,id'],
            'employee_id' => ['required', 'exists:employees,id'],
            'transferred_at' => ['required', 'date'],
            'purpose' => ['nullable', 'string'],
            'items' => ['required', 'array'],
            'items.*.item_id' => ['required_with:items', 'exists:items,id'],
            'items.*.amount' => ['required_with:items', 'integer', 'min:1'],
            'items.*.note' => ['nullable', 'string', 'max:225'],
        ]);

        try {
            DB::transaction(function () use ($data, $goodTransfer) {
                $goodTransfer->update([
                    'from_branch' => $data['from_branch_id'],
                    'to_branch' => $data['to_branch_id'],
                    'sent_by' => $data['employee_id'],
                    'transferred_at' => $data['transferred_at'],
                    'purpose' => $data['purpose'] ?? null,
                ]);

                $goodTransfer->items()->delete();

                foreach ($data['items'] as $item) {
                    $goodTransfer->items()->create([
                        'item_id' => $item['item_id'],
                        'quantity_transferred' => $item['amount'],
                        'note' => $item['note'] ?? null,
                    ]);
                }
            });

            app(CreateGoodTransferLogActivities::class)->execute(
                $goodTransfer->id,
                'Pemindahan Barang Diperbarui',
                'Pemindahan barang ' . $goodTransfer->transfer_no . ' berhasil diperbarui oleh ' . Auth::user()->employee?->name
            );

            return redirect()->route('good-transfers.index')->with('success', 'Pemindahan barang berhasil diperbarui!');
        } catch (Exception $e) {
            Log::error('Terjadi kesalahan saat memperbarui pemindahan barang', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'data' => $data
            ]);
            return back()->withInput()->with('error', 'Terjadi kesalahan saat memperbarui pemindahan barang. Silakan coba lagi.' . $e->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(GoodTransfer $goodTransfer)
    {
        abort_unless(Gate::allows('good_transfers.delete'), 403, 'Anda tidak memiliki akses untuk menghapus perpindahan barang');

        // Prevent deleting if status is Received or Canceled
        if (in_array($goodTransfer->status, ['Received', 'Canceled'])) {
            return back()->with('error', 'Pemindahan barang dengan status ' . $goodTransfer->status . ' tidak dapat dihapus.');
        }

        try {
            DB::transaction(function () use ($goodTransfer) {
                // Restore stock if status is Shipped (stock was already deducted when created)
                if ($goodTransfer->status === 'Shipped') {
                    $goodTransfer->load('items.item');

                    // Restore stock for each item (add back the stock that was deducted)
                    foreach ($goodTransfer->items as $transferItem) {
                        if ($transferItem->item) {
                            $stockInData = [
                                'type' => 'in',
                                'amount' => $transferItem->quantity_transferred,
                                'item_id' => $transferItem->item_id,
                                'source_type' => GoodTransfer::class,
                                'source_id' => $goodTransfer->id,
                                'tanggal' => now()->toDateString(),
                                'note' => 'Penghapusan pemindahan barang ' . $goodTransfer->transfer_no . ' - Stok dikembalikan',
                            ];

                            app(UpdateStockItem::class)->execute($stockInData, $transferItem->item);
                        }
                    }
                }

                $goodTransfer->items()->delete();
                $goodTransfer->delete();
            });

            return redirect()->route('good-transfers.index')->with('success', 'Pemindahan barang berhasil dihapus dan stok dikembalikan!');
        } catch (Exception $e) {
            Log::error('Terjadi kesalahan saat menghapus pemindahan barang', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'good_transfer_id' => $goodTransfer->id
            ]);
            return back()->with('error', 'Terjadi kesalahan saat menghapus pemindahan barang. Silakan coba lagi.');
        }
    }

    /**
     * Mark the good transfer as received.
     */
    public function receive(Request $request, GoodTransfer $goodTransfer)
    {
        abort_unless(Gate::allows('good_transfers.edit'), 403, 'Anda tidak memiliki akses untuk memproses perpindahan barang');

        try {
            app(ReceiveGoodTranfer::class)->execute($goodTransfer, $request->all());

            return redirect()->route('good-transfers.index')
                ->with('success', 'Pemindahan barang berhasil ditandai sebagai diterima!');
        } catch (Exception $e) {
            Log::error('Terjadi kesalahan saat menandai pemindahan barang sebagai diterima', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'good_transfer_id' => $goodTransfer->id
            ]);
            return back()->with('error', 'Terjadi kesalahan saat menandai pemindahan barang sebagai diterima. Silakan coba lagi.');
        }
    }

    /**
     * Cancel the good transfer.
     */
    public function cancel(Request $request, GoodTransfer $goodTransfer)
    {
        abort_unless(Gate::allows('good_transfers.edit'), 403, 'Anda tidak memiliki akses untuk membatalkan perpindahan barang');

        // Prevent canceling if already received or canceled
        if (in_array($goodTransfer->status, ['Received', 'Canceled'])) {
            return back()->with('error', 'Pemindahan barang dengan status ' . $goodTransfer->status . ' tidak dapat dibatalkan.');
        }

        try {
            DB::transaction(function () use ($goodTransfer) {
                // Only restore stock if status is Shipped (stock was already deducted)
                if ($goodTransfer->status === 'Shipped') {
                    $goodTransfer->load('items.item');

                    // Restore stock for each item (add back the stock that was deducted)
                    foreach ($goodTransfer->items as $transferItem) {
                        if ($transferItem->item) {
                            $stockInData = [
                                'type' => 'in',
                                'amount' => $transferItem->quantity_transferred,
                                'item_id' => $transferItem->item_id,
                                'source_type' => GoodTransfer::class,
                                'source_id' => $goodTransfer->id,
                                'tanggal' => now()->toDateString(),
                                'note' => 'Pembatalan pemindahan barang ' . $goodTransfer->transfer_no . ' - Stok dikembalikan',
                            ];

                            app(UpdateStockItem::class)->execute($stockInData, $transferItem->item);
                        }
                    }
                }

                $goodTransfer->update([
                    'status' => 'Canceled',
                ]);

                app(CreateGoodTransferLogActivities::class)->execute(
                    $goodTransfer->id,
                    'Pemindahan Barang Dibatalkan',
                    'Pemindahan barang ' . $goodTransfer->transfer_no . ' dibatalkan oleh ' . Auth::user()->employee?->name . ' dan stok dikembalikan'
                );
            });

            return redirect()->route('good-transfers.index')
                ->with('success', 'Pemindahan barang berhasil dibatalkan dan stok dikembalikan!');
        } catch (Exception $e) {
            Log::error('Terjadi kesalahan saat membatalkan pemindahan barang', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'good_transfer_id' => $goodTransfer->id
            ]);
            return back()->with('error', 'Terjadi kesalahan saat membatalkan pemindahan barang. Silakan coba lagi.');
        }
    }

    /**
     * Print receipt for the good transfer.
     */
    public function printReceipt(GoodTransfer $goodTransfer)
    {
        abort_unless(Gate::allows('good_transfers.view'), 403, 'Anda tidak memiliki akses untuk melihat perpindahan barang');

        $goodTransfer->load([
            'fromBranch',
            'toBranch:id,name',
            'sentBy:id,name',
            'receivedBy:id,name',
            'items.item:id,name,code'
        ]);

        return view('good-transfers.print-receipt', compact('goodTransfer'));
    }
}
