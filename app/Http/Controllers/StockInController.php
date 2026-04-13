<?php

namespace App\Http\Controllers;

use App\Actions\Data\Item\RecalculateItemStocks;
use App\Actions\Data\Item\UpdateStockItem as RecordStock;
use App\Models\Branch;
use App\Models\GoodTransfer;
use App\Models\Item;
use App\Models\ItemStock;
use App\Models\PurchaseOrder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class StockInController extends Controller
{
    /**
     * List stok masuk.
     */
    public function index(Request $request)
    {
        $user = Auth::user()->load('employee.branch');

        $filters = [
            'q' => $request->string('q')->toString(),
            'branch_id' => $request->integer('branch_id') ?: null,
            'item_id' => $request->integer('item_id') ?: null,
            'date_from' => $request->string('date_from')->toString(),
            'date_to' => $request->string('date_to')->toString(),
        ];

        // per_page: dukung pilihan jumlah data per halaman
        $allowedPerPage = [10, 15, 25, 50, 100];
        $perPageInput = (int) $request->integer('per_page');
        $perPage = in_array($perPageInput, $allowedPerPage, true) ? $perPageInput : 15;

        $sortBy = $request->get('sortBy', 'id');
        $sortDirection = $request->get('sortDirection', 'desc');
        $groupBy = $request->get('groupBy');

        $isSuperadmin = Auth::user()->hasRole("Superadmin");
        $userBranchId = Auth::user()->employee->branch_id ?? null;
        $branches     = Branch::where('id', $userBranchId)->pluck('id');

        // Terima kedua kemungkinan casing 'in'/'In'
        $query = ItemStock::whereIn('type', ['in', 'In'])
            ->with(['source', 'item.unit', 'item.branch'])
            ->when(!$isSuperadmin && $userBranchId != 2 && empty($filters['branch_id']), function ($query) use ($branches) {
                return $query->whereHas('item', function ($query) use ($branches) {
                    $query->whereIn('branch_id', $branches);
                });
            });

        // Pencarian nomor referensi (PO/Transfer)
        if ($filters['q']) {
            $search = $filters['q'];
            $query->where(function ($q) use ($search) {
                $q->whereHasMorph(
                    'source',
                    [PurchaseOrder::class],
                    fn($qq) => $qq->where('order_no', 'like', "%{$search}%")
                )->orWhereHasMorph(
                    'source',
                    [GoodTransfer::class],
                    fn($qq) => $qq->where('transfer_no', 'like', "%{$search}%")
                );
            });
        }

        if ($filters['item_id'])
            $query->where('item_id', $filters['item_id']);
        if ($filters['date_from'])
            $query->whereDate('created_at', '>=', $filters['date_from']);
        if ($filters['date_to'])
            $query->whereDate('created_at', '<=', $filters['date_to']);
        if ($filters['branch_id'])
            $query->whereRelation('item', 'branch_id', $filters['branch_id']);

        // Sorting (hindari 'reference_no' karena bukan kolom tabel langsung)
        $sortDirection = strtolower($sortDirection) === 'asc' ? 'asc' : 'desc';
        $validSortColumns = ['initial_stock', 'amount', 'last_stock', 'created_at', 'id'];
        if (in_array($sortBy, $validSortColumns, true)) {
            $query->orderBy($sortBy, $sortDirection);
        } else {
            $query->orderBy('id', $sortDirection);
        }

        $itemStocks = $query->paginate($perPage)->withQueryString();

        return Inertia::render('Admin/StockIns/Index', [
            'user' => $user,
            'isSuperadmin' => $isSuperadmin,
            'itemStocks' => $itemStocks->through(function ($gt) {
                return [
                    'id' => $gt->id,
                    'reference_no' => $gt->reference_no,
                    'item' => $gt->item,
                    'item_id' => $gt->item_id, // untuk modal edit
                    'initial_stock' => $gt->initial_stock,
                    'amount' => $gt->amount,
                    'last_stock' => $gt->last_stock,
                    'note' => $gt->note,
                    // tanggal ISO untuk input[type=date] di modal edit
                    'tanggal' => $gt->tanggal
                        ? \Carbon\Carbon::parse($gt->tanggal)->format('Y-m-d')
                        : $gt->created_at->format('Y-m-d'),
                    // tampilan lokal Indonesia untuk tabel
                    'created_at' => $gt->created_at->locale('id')->translatedFormat('d F Y'),
                ];
            }),
            'items'         => Item::when(!$isSuperadmin && $userBranchId != 2, function ($query) use ($branches) {
                return $query->whereIn('branch_id', $branches);
            })->with('branch')->get(),
            'branches' => Branch::get(),
            'filters' => array_merge($filters),
            'sortBy' => $sortBy,
            'sortDirection' => $sortDirection,
            'groupBy' => in_array($groupBy, ['ordered_by'], true) ? $groupBy : null,
            // Sidebar counters
            'sidebarCounts' => [
                'total' => ItemStock::whereIn('type', ['in', 'In'])->count(),
            ],
        ]);
    }

    /**
     * Simpan stok masuk baru (pakai Action).
     */
    public function store(Request $request, RecordStock $recordStock)
    {
        $validated = $request->validate([
            'item_id' => ['required', 'exists:items,id'],
            'amount' => ['required', 'numeric', 'min:0.0001'],
            'tanggal' => ['nullable', 'date'],
            'note' => ['nullable', 'string'],
        ]);

        return DB::transaction(function () use ($validated, $recordStock) {
            $item = Item::lockForUpdate()->findOrFail($validated['item_id']);

            // Catat transaksi stok masuk lewat Action
            $recordStock->execute([
                'type' => 'in', // disimpan lowercase; index() sudah menerima kedua casing
                'amount' => (float) $validated['amount'],
                'tanggal' => $validated['tanggal'] ?? null,
                'note' => $validated['note'] ?? null,
            ], $item);

            return back()->with('success', 'Stok masuk berhasil ditambahkan');
        });
    }

    /**
     * Update transaksi stok masuk (edit qty/tanggal/item) + recalc seluruh transaksi item terkait.
     */
    public function update(Request $request, $id, RecalculateItemStocks $recalc)
    {
        $validated = $request->validate([
            'item_id' => ['required', 'exists:items,id'],
            'amount' => ['required', 'numeric', 'min:0.0001'],
            'tanggal' => ['nullable', 'date'],
            'note' => ['nullable', 'string'],
        ]);

        return DB::transaction(function () use ($validated, $id, $recalc) {
            $stock = ItemStock::lockForUpdate()->findOrFail($id);

            // Pastikan ini memang transaksi stok masuk
            if (!in_array(strtolower((string) $stock->type), ['in', 'transfer_in'], true)) {
                return back()->with('error', 'Transaksi ini bukan stok masuk.');
            }

            $oldItemId = (int) $stock->item_id;

            $stock->item_id = (int) $validated['item_id'];
            $stock->amount = (float) $validated['amount'];
            $stock->note = $validated['note'] ?? null;

            if (!empty($validated['tanggal'])) {
                $stock->tanggal = $validated['tanggal'];
                $stock->created_at = $validated['tanggal']; // konsisten dengan store()
            }

            $stock->save();

            // Recalculate untuk menjaga konsistensi stok berurutan
            if ($oldItemId !== (int) $stock->item_id) {
                $recalc->execute($oldItemId);
            }
            $recalc->execute((int) $stock->item_id);

            return back()->with('success', 'Stok masuk berhasil diperbarui');
        });
    }

    /**
     * Hapus transaksi stok masuk + recalc sisa transaksi item terkait.
     */
    public function destroy($id, RecalculateItemStocks $recalc)
    {
        return DB::transaction(function () use ($id, $recalc) {
            $stock = ItemStock::lockForUpdate()->findOrFail($id);
            $itemId = (int) $stock->item_id;

            $stock->delete();

            // Hitung ulang setelah penghapusan
            $recalc->execute($itemId);

            return back()->with('success', 'Stok masuk berhasil dihapus');
        });
    }
}
