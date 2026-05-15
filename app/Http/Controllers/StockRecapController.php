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
        ];


        // Set default date range if not provided (use full datetime boundaries)
        $tanggalAwal = $filters['date_from'] ? \Carbon\Carbon::parse($filters['date_from'])->startOfDay() : now()->startOfMonth();
        $tanggalAkhir = $filters['date_to'] ? \Carbon\Carbon::parse($filters['date_to'])->endOfDay() : now()->endOfDay();

        $isSuperadmin = Auth::user()->hasRole("Superadmin");
        // Get all items with stock data grouped by item and branch
        $itemsQuery = Item::with(['category', 'unit', 'branch'])
            ->select('items.*')
            ->leftJoin('branches', 'items.branch_id', '=', 'branches.id')
            ->addSelect('branches.name as branch_name')
            ->when(!$isSuperadmin && Auth::user()->employee->branch_id != 2 && empty($filters['branch_id']), function ($query) {
                return $query->where('items.branch_id', Auth::user()->employee->branch_id);
            })
            // stok_awal (qty) before start date
            ->selectSub(function ($q) use ($tanggalAwal) {
                $q->from('item_stocks')
                    ->selectRaw("COALESCE(SUM(CASE WHEN type = 'In' THEN amount ELSE -amount END), 0)")
                    ->whereColumn('item_stocks.item_id', 'items.id')
                    ->where('item_stocks.created_at', '<', $tanggalAwal);
            }, 'stok_awal')
            // stok_masuk (qty) within range
            ->selectSub(function ($q) use ($tanggalAwal, $tanggalAkhir) {
                $q->from('item_stocks')
                    ->selectRaw('COALESCE(SUM(amount), 0)')
                    ->whereColumn('item_stocks.item_id', 'items.id')
                    ->where('type', 'In')
                    ->whereBetween('item_stocks.created_at', [$tanggalAwal, $tanggalAkhir]);
            }, 'stok_masuk')
            // stok_keluar (qty) within range
            ->selectSub(function ($q) use ($tanggalAwal, $tanggalAkhir) {
                $q->from('item_stocks')
                    ->selectRaw('COALESCE(SUM(amount), 0)')
                    ->whereColumn('item_stocks.item_id', 'items.id')
                    ->where('type', 'Out')
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
            // Order by stok_masuk DESC (highest first), then stok_keluar DESC
            ->orderByRaw('(
                COALESCE((SELECT SUM(amount) FROM item_stocks WHERE item_stocks.item_id = items.id AND type = "In" AND item_stocks.created_at BETWEEN ? AND ?), 0)
            ) DESC', [$tanggalAwal, $tanggalAkhir])
            ->orderByRaw('(
                COALESCE((SELECT SUM(amount) FROM item_stocks WHERE item_stocks.item_id = items.id AND type = "Out" AND item_stocks.created_at BETWEEN ? AND ?), 0)
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
                    'unit' => $item->unit ? $item->unit->name : null,
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
                $query->where('type', 'In')
                    ->whereBetween('created_at', [$tanggalAwal, $tanggalAkhir]);
                break;
            case 'stok_keluar':
                // Out transactions within range
                $query->where('type', 'Out')
                    ->whereBetween('created_at', [$tanggalAwal, $tanggalAkhir]);
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
                    'type' => $stock->type,
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
