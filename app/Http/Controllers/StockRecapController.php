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
        $tanggalAwal = $filters['date_from'] ? \Carbon\Carbon::parse($filters['date_from'])->startOfDay() : \Carbon\Carbon::create(2000, 1, 1)->startOfDay();
        $tanggalAkhir = $filters['date_to'] ? \Carbon\Carbon::parse($filters['date_to'])->endOfDay() : now()->endOfDay();
        $isSuperadmin = Auth::user()->hasRole("Superadmin");
        $canViewGlobal = Auth::user()->can('stock_recap.view_global') || $isSuperadmin;
        $userBranchId = Auth::user()->employee->branch_id ?? null;
        
        // Get all items with stock data grouped by item and branch
        $uniqueQuery = Item::query()
            ->select('items.name', 'items.category_id', 'items.unit_id', DB::raw('MIN(items.id) as id'))
            ->groupBy('items.name', 'items.category_id', 'items.unit_id')
            ->when($filters['item_id'], function ($q) use ($filters) {
                // If specific item is selected, we filter by name of that item
                $selectedItem = Item::find($filters['item_id']);
                if ($selectedItem) {
                    $q->where('items.name', $selectedItem->name);
                }
            })
            ->when($filters['category_id'], function ($q) use ($filters) {
                $q->where('items.category_id', $filters['category_id']);
            })
            ->when($filters['unit_id'], function ($q) use ($filters) {
                $q->where('items.unit_id', $filters['unit_id']);
            })
            ->when($filters['branch_id'], function ($q) use ($filters) {
                $q->whereExists(function ($sub) use ($filters) {
                    $sub->select(DB::raw(1))
                        ->from('items as sub_items')
                        ->whereColumn('sub_items.name', 'items.name')
                        ->whereColumn('sub_items.category_id', 'items.category_id')
                        ->whereColumn('sub_items.unit_id', 'items.unit_id')
                        ->where('sub_items.branch_id', $filters['branch_id']);
                });
            })
            ->when($filters['q'], function ($q) use ($filters) {
                $q->where('items.name', 'like', '%' . $filters['q'] . '%');
            })
            ->when($filters['hide_empty'], function ($query) use ($tanggalAwal, $tanggalAkhir) {
                $query->whereExists(function ($sub) use ($tanggalAwal, $tanggalAkhir) {
                    $sub->select(DB::raw(1))
                        ->from('items as sub_items')
                        ->whereColumn('sub_items.name', 'items.name')
                        ->whereColumn('sub_items.category_id', 'items.category_id')
                        ->whereColumn('sub_items.unit_id', 'items.unit_id')
                        ->whereRaw('(
                            COALESCE((SELECT SUM(CASE WHEN type IN ("In", "in", "adjust_in", "transfer_in", "return_in") THEN amount ELSE -amount END) FROM item_stocks WHERE item_stocks.item_id = sub_items.id AND item_stocks.created_at < ?), 0) +
                            COALESCE((SELECT SUM(amount) FROM item_stocks WHERE item_stocks.item_id = sub_items.id AND type IN ("In", "in", "adjust_in", "transfer_in", "return_in") AND item_stocks.created_at BETWEEN ? AND ?), 0) -
                            COALESCE((SELECT SUM(amount) FROM item_stocks WHERE item_stocks.item_id = sub_items.id AND type IN ("Out", "out", "adjust_out", "transfer_out") AND item_stocks.created_at BETWEEN ? AND ?), 0)
                        ) > 0', [$tanggalAwal, $tanggalAwal, $tanggalAkhir, $tanggalAwal, $tanggalAkhir]);
                });
            })
            ->when(!$canViewGlobal, function ($query) use ($userBranchId) {
                $query->whereExists(function ($sub) use ($userBranchId) {
                    $sub->select(DB::raw(1))
                        ->from('items as sub_items')
                        ->whereColumn('sub_items.name', 'items.name')
                        ->whereColumn('sub_items.category_id', 'items.category_id')
                        ->whereColumn('sub_items.unit_id', 'items.unit_id')
                        ->where('sub_items.branch_id', $userBranchId);
                });
            })
            ->orderBy('items.name', 'asc');

        $items = $uniqueQuery->paginate($request->integer('per_page') ?: 15)->withQueryString();

        $itemGroups = $items->getCollection();
        $names = $itemGroups->pluck('name')->unique()->toArray();

        // Query all matching items across branches with their stock details
        $allBranchItems = Item::whereIn('items.name', $names)
            ->select('items.*')
            ->leftJoin('branches', 'items.branch_id', '=', 'branches.id')
            ->addSelect('branches.name as branch_name')
            // stok_awal (qty) before start date
            ->selectSub(function ($q) use ($tanggalAwal) {
                $q->from('item_stocks')
                    ->selectRaw("COALESCE(SUM(CASE WHEN type IN ('In', 'in', 'adjust_in', 'transfer_in', 'return_in') THEN amount ELSE -amount END), 0)")
                    ->whereColumn('item_stocks.item_id', 'items.id')
                    ->where('item_stocks.created_at', '<', $tanggalAwal);
            }, 'stok_awal')
            // stok_masuk (qty) within range
            ->selectSub(function ($q) use ($tanggalAwal, $tanggalAkhir) {
                $q->from('item_stocks')
                    ->selectRaw('COALESCE(SUM(amount), 0)')
                    ->whereColumn('item_stocks.item_id', 'items.id')
                    ->whereIn('type', ['In', 'in', 'adjust_in', 'transfer_in', 'return_in'])
                    ->whereBetween('item_stocks.created_at', [$tanggalAwal, $tanggalAkhir]);
            }, 'stok_masuk')
            // stok_keluar (qty) within range
            ->selectSub(function ($q) use ($tanggalAwal, $tanggalAkhir) {
                $q->from('item_stocks')
                    ->selectRaw('COALESCE(SUM(amount), 0)')
                    ->whereColumn('item_stocks.item_id', 'items.id')
                    ->whereIn('type', ['Out', 'out', 'adjust_out', 'transfer_out'])
                    ->whereBetween('item_stocks.created_at', [$tanggalAwal, $tanggalAkhir]);
            }, 'stok_keluar')
            ->get();

        // Group the branch items by name, category_id, unit_id for easy lookup
        $branchItemsGrouped = $allBranchItems->groupBy(function ($item) {
            return $item->name . '_' . $item->category_id . '_' . $item->unit_id;
        });

        // Load relationships for the paginated item groups
        $itemGroups->load(['category', 'unit']);

        $data = $itemGroups->map(function ($group) use ($branchItemsGrouped) {
            $lookupKey = $group->name . '_' . $group->category_id . '_' . $group->unit_id;
            $itemsForGroup = $branchItemsGrouped->get($lookupKey) ?: collect();

            $stocksByBranch = [];
            $branchesData = [];
            $totalPersediaan = 0;

            foreach ($itemsForGroup as $item) {
                $stokAwal = (float) ($item->stok_awal ?? 0);
                $stokMasuk = (float) ($item->stok_masuk ?? 0);
                $stokKeluar = (float) ($item->stok_keluar ?? 0);
                $stokAkhir = $stokAwal + $stokMasuk - $stokKeluar;

                $stocksByBranch[$item->branch_id] = [
                    'item_id' => $item->id,
                    'qty' => $stokAkhir,
                ];

                $branchesData[] = [
                    'item_id' => $item->id,
                    'branch_id' => $item->branch_id,
                    'branch_name' => $item->branch_name ?? 'Tanpa Cabang',
                ];

                $totalPersediaan += $stokAkhir;
            }

            return [
                'id' => $group->id,
                'item' => [
                    'id' => $group->id,
                    'code' => $group->code,
                    'name' => $group->name,
                    'unit' => $group->unit ? ($group->unit->short_name ?? $group->unit->name) : null,
                    'price' => 0,
                    'category' => $group->category ? $group->category->name : null,
                ],
                'stocks' => $stocksByBranch,
                'branches_data' => $branchesData,
                'total_persediaan' => $totalPersediaan,
            ];
        });

        $items->setCollection($data);

        $branchesQuery = \App\Models\Branch::select('id', 'name');
        if (!$canViewGlobal) {
            $branchesQuery->where('id', $userBranchId);
        }
        $branches = $branchesQuery->get();

        return Inertia::render('Admin/StockRecap/Index', [
            'recaps' => $items,
            'filters' => $filters,
            'items' => Item::select('id', 'name')->get(),
            'categories' => ItemCategory::select('id', 'name')->get(),
            'units' => Unit::select('id', 'name')->get(),
            'branches' => $branches,
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
        $tanggalAwal = $dateFrom ? \Carbon\Carbon::parse($dateFrom)->startOfDay() : \Carbon\Carbon::create(2000, 1, 1)->startOfDay();
        $tanggalAkhir = $dateTo ? \Carbon\Carbon::parse($dateTo)->endOfDay() : now()->endOfDay();

        // Fetch ALL transactions for the item up to $tanggalAkhir
        // to accurately calculate the running stock in chronological order
        $allTransactions = ItemStock::with('source')
            ->where('item_id', $itemId)
            ->where('created_at', '<=', $tanggalAkhir)
            ->orderBy('created_at', 'asc')
            ->orderBy('id', 'asc')
            ->get();

        $runningStock = 0;
        
        $processedTransactions = $allTransactions->map(function ($stock) use (&$runningStock) {
            $is_in = in_array(strtolower((string) $stock->type), ['in', 'adjust_in', 'transfer_in', 'return_in']);
            $amount = (float) $stock->amount;
            
            $initial = $runningStock;
            if ($is_in) {
                $runningStock += $amount;
            } else {
                $runningStock -= $amount;
            }

            return (object) [
                'stock' => $stock,
                'is_in' => $is_in,
                'initial_stock' => $initial,
                'last_stock' => $runningStock,
            ];
        });

        // Filter based on requested type
        $filteredTransactions = $processedTransactions->filter(function ($item) use ($type, $tanggalAwal, $tanggalAkhir) {
            $createdAt = $item->stock->created_at;
            
            switch ($type) {
                case 'saldo_awal':
                    return $createdAt->lt($tanggalAwal);
                case 'stok_masuk':
                    return $item->is_in && $createdAt->between($tanggalAwal, $tanggalAkhir);
                case 'stok_keluar':
                    return !$item->is_in && $createdAt->between($tanggalAwal, $tanggalAkhir);
                case 'total_persediaan':
                    return $createdAt->between($tanggalAwal, $tanggalAkhir);
                default:
                    return $createdAt->between($tanggalAwal, $tanggalAkhir);
            }
        });

        // Format for output and reverse order (newest first)
        $transactions = $filteredTransactions->reverse()->values()->map(function ($item) {
            $stock = $item->stock;
            return [
                'id' => $stock->id,
                'type' => $item->is_in ? 'In' : 'Out',
                'original_type' => $stock->type,
                'source_type' => $this->getType($stock),
                'source_id' => $stock->source_id,
                'reference' => $this->getReference($stock),
                'initial_stock' => $item->initial_stock,
                'amount' => (float) $stock->amount,
                'last_stock' => $item->last_stock,
                'note' => $stock->note ?? '-',
                'transaction_value' => $this->getTransactionValue($stock),
                'created_at' => $stock->created_at->format('Y-m-d H:i:s'),
            ];
        });

        return response()->json([
            'transactions' => $transactions,
            'total' => $transactions->count(),
            'true_sisa_stok' => $runningStock
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
        // Guard: relasi item bisa null jika barang sudah diarsipkan (soft delete).
        // Kolom price tidak ada di tabel items, sehingga nilai transaksi selalu 0.
        return $stock->amount * ($stock->item?->price ?? 0);
    }
}
