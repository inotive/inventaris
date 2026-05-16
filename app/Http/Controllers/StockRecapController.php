<?php

namespace App\Http\Controllers;

use App\Models\GoodIssue;
use App\Models\GoodReceipt;
use App\Models\GoodTransfer;
use App\Models\Item;
use App\Models\ItemCategory;
use App\Models\ItemStock;
use App\Models\PurchaseOrder;
use App\Models\Unit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class StockRecapController extends Controller
{
    public function index(Request $request)
    {
        $filters = [
            'date_from' => $request->input('date_from'),
            'date_to' => $request->input('date_to'),
            'item_id' => $request->integer('item_id') ?: null,
            'category_id' => $request->integer('category_id') ?: null,
            'unit_id' => $request->integer('unit_id') ?: null,
            'branch_id' => $request->integer('branch_id') ?: null,
            'q' => (string) $request->get('q', ''),
            'hide_empty' => $request->boolean('hide_empty'),
        ];


        // Set default date range if not provided (use full datetime boundaries)
        $tanggalAwal = $filters['date_from'] ? \Carbon\Carbon::parse($filters['date_from'])->startOfDay() : now()->startOfMonth();
        $tanggalAkhir = $filters['date_to'] ? \Carbon\Carbon::parse($filters['date_to'])->endOfDay() : now()->endOfDay();

        $isSuperadmin = Auth::user()->hasRole("Superadmin");
        $canViewGlobal = Auth::user()->can('stock_recap.view_global') || $isSuperadmin;
        $userBranchId = Auth::user()->employee->branch_id ?? null;
        
        // Get all items with stock data grouped by item and branch
        $itemsQuery = Item::with(['category', 'unit', 'branch'])
            ->select('items.*')
            ->leftJoin('branches', 'items.branch_id', '=', 'branches.id')
            ->addSelect('branches.name as branch_name')
            // stok_awal (qty) before start date
            ->selectSub(function ($q) use ($tanggalAwal) {
                $q->from('item_stocks')
                    ->selectRaw("COALESCE(SUM(CASE WHEN type IN ('In', 'adjust_in', 'transfer_in') THEN amount ELSE -amount END), 0)")
                    ->whereColumn('item_stocks.item_id', 'items.id')
                    ->where('item_stocks.created_at', '<', $tanggalAwal);
            }, 'stok_awal')
            // stok_masuk (qty) within range
            ->selectSub(function ($q) use ($tanggalAwal, $tanggalAkhir) {
                $q->from('item_stocks')
                    ->selectRaw('COALESCE(SUM(amount), 0)')
                    ->whereColumn('item_stocks.item_id', 'items.id')
                    ->whereIn('type', ['In', 'adjust_in', 'transfer_in'])
                    ->whereBetween('item_stocks.created_at', [$tanggalAwal, $tanggalAkhir]);
            }, 'stok_masuk')
            // stok_keluar (qty) within range
            ->selectSub(function ($q) use ($tanggalAwal, $tanggalAkhir) {
                $q->from('item_stocks')
                    ->selectRaw('COALESCE(SUM(amount), 0)')
                    ->whereColumn('item_stocks.item_id', 'items.id')
                    ->whereIn('type', ['Out', 'adjust_out', 'transfer_out'])
                    ->whereBetween('item_stocks.created_at', [$tanggalAwal, $tanggalAkhir]);
            }, 'stok_keluar')
            // Nominal perhitungan dinonaktifkan (tidak ada kolom items.price)
            ->when($filters['item_id'], function ($q) use ($filters) {
                $q->where('items.id', $filters['item_id']);
            })
            ->when($filters['category_id'], function ($q) use ($filters) {
                // filter by single category relation
                $q->where('items.category_id', $filters['category_id']);
            })
            ->when($filters['unit_id'], function ($q) use ($filters) {
                // filter by unit
                $q->where('items.unit_id', $filters['unit_id']);
            })
            ->when($filters['branch_id'], function ($q) use ($filters) {
                // filter by branch
                $q->where('items.branch_id', $filters['branch_id']);
            })
            ->when($filters['q'], function ($q) use ($filters) {
                $q->where('items.name', 'like', '%' . $filters['q'] . '%');
            })
            ->when($filters['hide_empty'], function ($query) use ($tanggalAwal, $tanggalAkhir) {
                // If hide_empty is true, ONLY show items where ending stock is > 0
                $query->whereRaw('(
                        COALESCE((SELECT SUM(CASE WHEN type IN ("In", "adjust_in", "transfer_in") THEN amount ELSE -amount END) FROM item_stocks WHERE item_stocks.item_id = items.id AND item_stocks.created_at < ?), 0) +
                        COALESCE((SELECT SUM(amount) FROM item_stocks WHERE item_stocks.item_id = items.id AND type IN ("In", "adjust_in", "transfer_in") AND item_stocks.created_at BETWEEN ? AND ?), 0) -
                        COALESCE((SELECT SUM(amount) FROM item_stocks WHERE item_stocks.item_id = items.id AND type IN ("Out", "adjust_out", "transfer_out") AND item_stocks.created_at BETWEEN ? AND ?), 0)
                    ) > 0', [$tanggalAwal, $tanggalAwal, $tanggalAkhir, $tanggalAwal, $tanggalAkhir]);
            })
            ->when(!$canViewGlobal && empty($filters['branch_id']), function ($query) use ($userBranchId) {
                // Only restrict to user's branch if they DON'T have global view permission
                return $query->where('items.branch_id', $userBranchId);
            })
            // Order by stok_masuk DESC (highest first), then stok_keluar DESC
            ->orderByRaw('(
                COALESCE((SELECT SUM(amount) FROM item_stocks WHERE item_stocks.item_id = items.id AND type IN ("In", "adjust_in", "transfer_in") AND item_stocks.created_at BETWEEN ? AND ?), 0)
            ) DESC', [$tanggalAwal, $tanggalAkhir])
            ->orderByRaw('(
                COALESCE((SELECT SUM(amount) FROM item_stocks WHERE item_stocks.item_id = items.id AND type IN ("Out", "adjust_out", "transfer_out") AND item_stocks.created_at BETWEEN ? AND ?), 0)
            ) DESC', [$tanggalAwal, $tanggalAkhir]);

        $items = $itemsQuery->paginate($request->integer('per_page') ?: 15)->withQueryString();

        $data = $items->getCollection()->map(function ($item) use ($tanggalAwal, $tanggalAkhir) {
            // Use values from subqueries that respect date filters
            $stokAwal = (float) ($item->stok_awal ?? 0);
            $stokMasuk = (float) ($item->stok_masuk ?? 0);
            $stokKeluar = (float) ($item->stok_keluar ?? 0);

            // Calculate ending stock based on date range: saldo awal + masuk - keluar
            $stokAkhir = $stokAwal + $stokMasuk - $stokKeluar;

            // Calculate activity (total movement)
            $aktivitas = $stokMasuk + $stokKeluar;

            return [
                'id' => $item->id,
                'item' => [
                    'id' => $item->id,
                    'code' => $item->code,
                    'name' => $item->name,
                    'unit' => $item->unit ? ($item->unit->short_name ?? $item->unit->name) : null,
                    // price & categories di-nonaktifkan/diringkas
                    'price' => 0,
                    'category' => $item->category ? $item->category->name : null,
                ],
                'branch' => $item->branch_name ?? 'Semua Cabang',
                'saldo_awal' => [
                    'qty' => $stokAwal,
                    'value' => 0,
                ],
                'in' => [
                    'qty' => $stokMasuk,
                    'value' => 0,
                ],
                'out' => [
                    'qty' => $stokKeluar,
                    'value' => 0,
                ],
                'ending' => [
                    'qty' => $stokAkhir,
                    'value' => 0
                ],
                'aktivitas' => $aktivitas, // Total pergerakan (masuk + keluar)
            ];
        });

        $items->setCollection($data);

        return Inertia::render('Admin/StockRecap/Index', [
            'recaps' => $items,
            'filters' => $filters,
            'items' => Item::select('id', 'name')->get(),
            'categories' => ItemCategory::select('id', 'name')->get(),
            'units' => Unit::select('id', 'name')->get(),
            'branches' => \App\Models\Branch::select('id', 'name')->get(),
            'can' => [
                'adjust' => Auth::user()->can('stock_recap.adjust') || $isSuperadmin,
            ]
        ]);
    }

    public function detail(Request $request)
    {
        $itemId = $request->integer('item_id');
        $type = $request->input('type');
        $dateFrom = $request->input('date_from');
        $dateTo = $request->input('date_to');

        // Set default date range
        $tanggalAwal = $dateFrom ? \Carbon\Carbon::parse($dateFrom)->startOfDay() : now()->startOfMonth();
        $tanggalAkhir = $dateTo ? \Carbon\Carbon::parse($dateTo)->endOfDay() : now()->endOfDay();

        $query = ItemStock::where('item_id', $itemId);

        // Filter based on type
        switch ($type) {
            case 'saldo_awal':
                // Transactions before start date
                $query->where('created_at', '<', $tanggalAwal);
                break;
            case 'stok_masuk':
                // In transactions within range
                $query->whereIn('type', ['In', 'in', 'adjust_in', 'transfer_in', 'return_in'])
                    ->whereBetween('item_stocks.created_at', [$tanggalAwal, $tanggalAkhir]);
                break;
            case 'stok_keluar':
                // Out transactions within range
                $query->whereIn('type', ['Out', 'out', 'adjust_out', 'transfer_out'])
                    ->whereBetween('item_stocks.created_at', [$tanggalAwal, $tanggalAkhir]);
                break;
            case 'total_persediaan':
                // All transactions up to end date
                $query->where('created_at', '<=', $tanggalAkhir);
                break;
            default:
                // All transactions within range
                $query->whereBetween('created_at', [$tanggalAwal, $tanggalAkhir]);
        }

        $transactions = $query->with('source')
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($stock) {
                return [
                    'id' => $stock->id,
                    'type' => in_array(strtolower((string) $stock->type), ['in', 'adjust_in', 'transfer_in', 'return_in']) ? 'In' : 'Out',
                    'original_type' => $stock->type,
                    'source_type' => $this->getType($stock),
                    'source_id' => $stock->source_id,
                    'reference' => $this->getReference($stock),
                    'initial_stock' => $stock->initial_stock,
                    'amount' => $stock->amount,
                    'last_stock' => $stock->last_stock,
                    'note' => $stock->note ?? '-',
                    'transaction_value' => $this->getTransactionValue($stock),
                    'created_at' => $stock->created_at->format('Y-m-d H:i:s'),
                ];
            });

        return response()->json([
            'transactions' => $transactions,
            'total' => $transactions->count(),
        ]);
    }

    public function adjust(Request $request)
    {
        if (!Auth::user()->can('stock_recap.adjust') && !Auth::user()->hasRole('Superadmin')) {
            abort(403, 'Anda tidak memiliki hak akses untuk melakukan penyesuaian stok.');
        }

        $request->validate([
            'item_id' => 'required|exists:items,id',
            'actual_stock' => 'required|numeric|min:0',
            'note' => 'nullable|string'
        ]);

        return \Illuminate\Support\Facades\DB::transaction(function () use ($request) {
            $item = Item::findOrFail($request->item_id);
            $lastStockRecord = $item->stocks()->orderByDesc('id')->first();
            $currentStock = $lastStockRecord?->last_stock ?? 0;
            $actualStock = $request->actual_stock;
            $difference = $actualStock - $currentStock;

            if ($difference == 0) {
                return back()->with('success', 'Stok sudah sesuai, tidak ada penyesuaian yang dilakukan.');
            }

            $type = $difference > 0 ? 'adjust_in' : 'adjust_out';
            $amount = abs($difference);

            $stockData = [
                'type' => $type,
                'amount' => $amount,
                'tanggal' => now()->toDateString(),
                'note' => $request->note ?? 'Penyesuaian stok (Opname)'
            ];

            app(\App\Actions\Data\Item\UpdateStockItem::class)->execute($stockData, $item);

            return back()->with('success', 'Penyesuaian stok berhasil disimpan.');
        });
    }

    private function getReference($stock)
    {
        if (!$stock->source_type || !$stock->source_id) {
            return '-';
        }

        // Ensure source relationship is loaded
        if (!$stock->relationLoaded('source')) {
            $stock->load('source');
        }

        // Try to get reference from source
        $source = $stock->source;
        if ($source) {
            switch ($stock->source_type) {
                case PurchaseOrder::class:
                    return $source->order_no ?? '-';
                case GoodTransfer::class:
                    return $source->transfer_no ?? '-';
                case GoodIssue::class:
                    return $source->issue_no ?? '-';
                case GoodReceipt::class:
                    return $source->receipt_no ?? '-';
                default:
                    // Try common reference fields if they exist
                    if (isset($source->code)) {
                        return $source->code;
                    }
                    if (isset($source->reference)) {
                        return $source->reference;
                    }
                    if (isset($source->number)) {
                        return $source->number;
                    }
                    break;
            }
        }

        // Fallback: return source type and id
        $sourceTypeName = class_basename($stock->source_type);
        return $sourceTypeName . ' #' . $stock->source_id;
    }

    private function getType($stock)
    {
        $type = strtolower((string) $stock->type);
        if (str_contains($type, 'adjust')) {
            return 'Penyesuaian';
        }

        if (!$stock->source_type || !$stock->source_id) {
            return 'Manual';
        }

        // Ensure source relationship is loaded
        if (!$stock->relationLoaded('source')) {
            $stock->load('source');
        }

        // Try to get reference from source
        $source = $stock->source;
        if ($source) {
            switch ($stock->source_type) {
                case PurchaseOrder::class:
                    return "Pembelian Barang";
                case GoodTransfer::class:
                    return "Transfer Barang";
                case GoodIssue::class:
                    return "Pemakaian Barang";
                case GoodReceipt::class:
                    return "Penerimaan Barang";
                default:
                    return "Manual";
            }
        }

        return "Manual";
    }

    private function getTransactionValue($stock)
    {
        return $stock->amount * $stock->item->price;
    }
}
