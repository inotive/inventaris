<?php

namespace App\Http\Controllers;

use App\Actions\Data\Item\RecalculateItemStocks;
use App\Actions\Data\Item\UpdateStockItem as RecordStock;
use App\Models\Branch;
use App\Models\GoodIssue;
use App\Models\GoodTransfer;
use App\Models\Item;
use App\Models\ItemStock;
use App\Models\PurchaseOrder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class StockOutController extends Controller
{
    /**
     * List stok keluar.
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

        // Terima kedua kemungkinan casing 'out'/'Out'
        $query = ItemStock::whereIn('type', ['out', 'Out'])
            ->with(['source', 'item.unit', 'item.branch'])
            ->when(!$isSuperadmin && $userBranchId != 2 && empty($filters['branch_id']), function ($query) use ($branches) {
                return $query->whereHas('item', function ($query) use ($branches) {
                    $query->whereIn('branch_id', $branches);
                });
            });

        // Pencarian reference_no (PO / Transfer / Issue)
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
                )->orWhereHasMorph(
                    'source',
                    [GoodIssue::class],
                    fn($qq) => $qq->where('issue_no', 'like', "%{$search}%")
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

        // Sorting
        $sortDirection = strtolower($sortDirection) === 'asc' ? 'asc' : 'desc';
        $validSortColumns = ['initial_stock', 'amount', 'last_stock', 'created_at', 'id'];
        if (in_array($sortBy, $validSortColumns, true)) {
            $query->orderBy($sortBy, $sortDirection);
        } else {
            $query->orderBy('id', $sortDirection);
        }

        $itemStocks = $query->paginate($perPage)->withQueryString();

        $item = Item::when(!$isSuperadmin && $userBranchId != 2, function ($query) use ($branches) {
            return $query->whereIn('branch_id', $branches);
        })->with('stock', 'branch')->get()->map(function ($item) {
            $item->current_stock = $item->stock?->last_stock ?? 0;
            return $item;
        });

        return Inertia::render('Admin/StockOuts/Index', [
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
                    // tanggal ISO (untuk input date)
                    'tanggal' => $gt->tanggal
                        ? \Carbon\Carbon::parse($gt->tanggal)->format('Y-m-d')
                        : $gt->created_at->format('Y-m-d'),
                    // tampilan lokal untuk tabel
                    'created_at' => $gt->created_at->locale('id')->translatedFormat('d F Y'),
                ];
            }),
            'items' => $item,
            'branches' => Branch::get(),
            'filters' => array_merge($filters),
            'sortBy' => $sortBy,
            'sortDirection' => $sortDirection,
            'groupBy' => in_array($groupBy, ['ordered_by'], true) ? $groupBy : null,
            'sidebarCounts' => [
                'total' => ItemStock::whereIn('type', ['out', 'Out'])->count(),
            ],
        ]);
    }

    /**
     * Simpan stok keluar baru (pakai Action).
     */
    public function store(Request $request, RecordStock $recordStock)
    {
        $validated = $request->validate([
            'item_id' => ['required', 'exists:items,id'],
            'amount' => ['required', 'numeric', 'min:0.0001'],
            'tanggal' => ['nullable', 'date'],
            'note' => ['required', 'string'],
        ]);

        return DB::transaction(function () use ($validated, $recordStock) {
            $item = Item::lockForUpdate()->with('stock')->findOrFail($validated['item_id']);

            // Ambil stock saat ini
            $currentStock = $item->stock?->last_stock ?? 0;

            // Validasi: qty tidak boleh melebihi stock saat ini
            if ((float) $validated['amount'] > $currentStock) {
                return back()->withErrors([
                    'amount' => "Qty tidak boleh melebihi stock saat ini ({$currentStock})."
                ]);
            }

            // Catat transaksi stok keluar lewat Action
            $recordStock->execute([
                'type' => 'out', // lowercase
                'amount' => (float) $validated['amount'],
                'tanggal' => $validated['tanggal'] ?? null,
                'note' => $validated['note'] ?? null,
            ], $item);

            return back()->with('success', 'Stok keluar berhasil ditambahkan');
        });
    }

    /**
     * Update transaksi stok keluar (edit qty/tanggal/item) + recalc seluruh transaksi item terkait.
     */
    public function update(Request $request, $id, RecalculateItemStocks $recalc)
    {
        $validated = $request->validate([
            'item_id' => ['required', 'exists:items,id'],
            'amount' => ['required', 'numeric', 'min:0.0001'],
            'tanggal' => ['nullable', 'date'],
            'note' => ['required', 'string'],
        ]);

        return DB::transaction(function () use ($validated, $id, $recalc) {
            $stock = ItemStock::lockForUpdate()->findOrFail($id);

            // Pastikan ini memang transaksi stok keluar
            if (!in_array(strtolower((string) $stock->type), ['out', 'transfer_out'], true)) {
                return back()->with('error', 'Transaksi ini bukan stok keluar.');
            }

            $oldItemId = (int) $stock->item_id;
            $newItemId = (int) $validated['item_id'];

            // Ambil item yang akan diupdate
            $item = Item::lockForUpdate()->with('stock')->findOrFail($newItemId);

            // Ambil stock saat ini
            $currentStock = $item->stock?->last_stock ?? 0;

            // Untuk edit: tambahkan kembali amount lama jika item sama, kemudian kurangi dengan amount baru
            $adjustedStock = $currentStock;
            if ($oldItemId === $newItemId) {
                // Jika item sama, tambahkan kembali amount lama
                $adjustedStock = $currentStock + (float) $stock->amount;
            }

            // Validasi: qty baru tidak boleh melebihi stock yang sudah disesuaikan
            if ((float) $validated['amount'] > $adjustedStock) {
                return back()->withErrors([
                    'amount' => "Qty tidak boleh melebihi stock saat ini ({$adjustedStock})."
                ]);
            }

            $stock->item_id = $newItemId;
            $stock->amount = (float) $validated['amount'];
            $stock->note = $validated['note'] ?? null;

            if (!empty($validated['tanggal'])) {
                $stock->tanggal = $validated['tanggal'];
                $stock->created_at = $validated['tanggal']; // konsisten dengan store()
            }

            $stock->save();

            // Recalculate untuk menjaga konsistensi stok berurutan
            if ($oldItemId !== $newItemId) {
                $recalc->execute($oldItemId);
            }
            $recalc->execute($newItemId);

            return back()->with('success', 'Stok keluar berhasil diperbarui');
        });
    }

    /**
     * Hapus transaksi stok keluar + recalc sisa transaksi item terkait.
     */
    public function destroy($id, RecalculateItemStocks $recalc)
    {
        return DB::transaction(function () use ($id, $recalc) {
            $stock = ItemStock::lockForUpdate()->findOrFail($id);
            $itemId = (int) $stock->item_id;

            $stock->delete();
            $recalc->execute($itemId);

            return back()->with('success', 'Stok keluar berhasil dihapus');
        });
    }
}
