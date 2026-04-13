<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Item;
use App\Models\ItemMovement;
use Illuminate\Support\Facades\DB;
use App\Http\Resources\ItemResource;
use App\Helpers\ResponseFormatter;
use App\Http\Resources\ItemMovementResource;

class ItemController extends Controller
{
    // GET /api/v1/items
    public function index(Request $request)
    {
        $q = (string) $request->get('q', '');
        $perPage = (int) $request->get('per_page', 15);
        $onlyLowStock = (int) $request->get('low_stock', 0) === 1;
        $branchId = $request->integer('branch_id') ?: null;

        // Monthly window
        $month = (int) $request->get('month', (int) date('n'));
        $year = (int) $request->get('year', (int) date('Y'));
        $search = $request->get('search', '');
        $inTypes = ['in', 'adjust_in', 'transfer_in', 'return_in'];
        $outTypes = ['out', 'adjust_out', 'transfer_out', 'return_out'];

        // Base filter used for metrics
        $base = Item::query()
            ->with(['branch:id,name']) // Eager load branch to prevent N+1 query
            ->when($q !== '', function ($qb) use ($q) {
                $qb->where(function ($w) use ($q) {
                    $w->where('name', 'like', "%$q%")
                        ->orWhere('code', 'like', "%$q%");
                });
            })
            ->when($branchId, function ($qb) use ($branchId) {
                $qb->where('branch_id', $branchId);
            })
            ->when(function ($query) {
                $user = auth()->user();
                $isSuperadmin = $user->hasRole('Superadmin');
                $isBranch2 = ($user->employee && $user->employee->branch_id == 2);

                if (!$isSuperadmin && !$isBranch2 && $user->employee) {
                    $query->where('branch_id', $user->employee->branch_id);
                }
            });

        // Calculate low stock items by comparing current stock with min_stock
        $lowQuantityItems = (clone $base)
            ->whereExists(function ($query) {
                $query->select(DB::raw('1'))
                    ->from('item_stocks')
                    ->whereColumn('items.id', 'item_stocks.item_id')
                    ->groupBy('item_id')
                    ->havingRaw('SUM(CASE WHEN type = "in" THEN amount ELSE -amount END) <= (SELECT min_stock FROM items WHERE items.id = item_stocks.item_id)');
            })->count();

        // List data (can be further filtered by onlyLowStock)
        $items = (clone $base)
            ->withSum(['stocks as in_month' => function ($q) use ($month, $year, $inTypes) {
                $q->whereYear('created_at', $year)->whereMonth('created_at', $month)->whereIn('type', $inTypes);
            }], 'amount')
            ->withSum(['stocks as out_month' => function ($q) use ($month, $year, $outTypes) {
                $q->whereYear('created_at', $year)->whereMonth('created_at', $month)->whereIn('type', $outTypes);
            }], 'amount')
            ->when($onlyLowStock, function ($qb) {
                $qb->whereExists(function ($query) {
                    $query->select(DB::raw('1'))
                        ->from('item_stocks')
                        ->whereColumn('items.id', 'item_stocks.item_id')
                        ->groupBy('item_id')
                        ->havingRaw('SUM(CASE WHEN type = "in" THEN amount ELSE -amount END) <= (SELECT min_stock FROM items WHERE items.id = item_stocks.item_id)');
                });
            })
            ->when($search != '', function ($qb) use ($search) {
                $qb->where('name', 'like', "%$search%")
                    ->orWhere('code', 'like', "%$search%");
            })
            ->orderBy('name')
            ->paginate($perPage)
            ->withQueryString();

        return ResponseFormatter::success([
            'items' => ItemResource::collection($items),
            'total_items' => $items->count(),
            'low_quantity_items' => $lowQuantityItems,
            'branches' => \App\Models\Branch::select('id', 'name')->get(),
            'meta' => [
                'total' => $items->total(),
                'count' => $items->count(),
                'per_page' => $items->perPage(),
                'current_page' => $items->currentPage(),
                'last_page' => $items->lastPage(),
            ],
        ], 'Items fetched successfully');
    }

    // GET /api/v1/items/{id}
    public function show($id, Request $request)
    {
        $item = Item::with(['stock', 'branch:id,name'])->findOrFail($id);

        // Filters
        $dateFrom = $request->get('date_from');
        $dateTo = $request->get('date_to');

        // Single query for both totals and movements
        $movements = \App\Models\ItemStock::where('item_id', $item->id)
            ->when($dateFrom, function ($qb) use ($dateFrom) {
                $qb->whereDate('created_at', '>=', $dateFrom);
            })
            ->when($dateTo, function ($qb) use ($dateTo) {
                $qb->whereDate('created_at', '<=', $dateTo);
            })
            ->orderByDesc('created_at')
            ->get();

        // Calculate totals from the collection
        $totalMasuk = $movements->where('type', 'in')->sum('amount');
        $totalKeluar = $movements->where('type', 'out')->sum('amount');

        // Map riwayat_stock using ItemStock columns
        $riwayat = $movements->map(function ($m) {
            return [
                'tanggal' => $m->created_at->format('Y-m-d H:i:s'),
                'reference_type' => $this->translateSourceType((string) $m->source_type),
                'type' => $m->type === 'In' ? 'Masuk' : 'Keluar',
                'amount' => (float) $m->amount,
                'last_stock' => (float) ($m->last_stock ?? 0),
            ];
        });

        // Hitung stok gudang
        // Karena item_stocks tidak punya branch_id, stok adalah global
        // Untuk sementara, tampilkan total stock saja
        $currentStock = (float) ($item->stock->last_stock ?? 0);

        $stockPerBranch = [
            [
                'branch_id' => $item->branch_id ?? null,
                'branch_name' => $item->branch?->name ?? 'Gudang Utama',
                'stock' => $currentStock,
            ]
        ];

        return response()->json([
            'id' => $item->id,
            'nama_item' => $item->name,
            'kode_item' => $item->code,
            'total_masuk' => (float) $totalMasuk,
            'total_keluar' => (float) $totalKeluar,
            'riwayat_stock' => $riwayat,
            'stok_gudang' => $stockPerBranch,
            'current_stock' => (float) ($item->stock->last_stock ?? 0),
        ]);
    }

    private function translateSourceType(string $sourceType): string
    {
        $basename = class_basename($sourceType);

        // Add spaces before capital letters and translate to Indonesian
        $spaced = preg_replace('/([A-Z])/', ' $1', $basename);
        $spaced = trim($spaced);

        $translations = [
            'Purchase Order' => 'Pembelian Barang',
            'Good Transfer' => 'Transfer Barang',
            'Good Issue' => 'Pengeluaran Barang',
            'Stock Adjustment' => 'Penyesuaian Stok',
            'Sales Order' => 'Penjualan Barang',
        ];

        return $translations[$spaced] ?? $spaced;
    }
}
