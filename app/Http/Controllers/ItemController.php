<?php

namespace App\Http\Controllers;

use Throwable;
use App\Models\Item;
use App\Models\Unit;
use Inertia\Inertia;
use App\Models\Branch;
use App\Models\Department;
use App\Models\ItemCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class ItemController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user()->load('employee.branch');

        $perPage = (int) $request->input('per_page', 10);
        $isSuperadmin = Auth::user()->hasRole("Superadmin");
        $userBranchId = Auth::user()->employee->branch_id ?? null;

        $filters = [
            'q' => $request->string('q')->toString(),
            'code' => $request->string('code')->toString(),
            'name' => $request->string('name')->toString(),
            'unit_id' => $request->integer('unit_id') ?: null,
            'branch_id' => $request->has('branch_id') 
                ? ($request->input('branch_id') !== '' ? (int) $request->input('branch_id') : null) 
                : ($isSuperadmin ? null : $userBranchId),
            'item_id' => $request->integer('item_id') ?: null,
            'category_id' => $request->integer('category_id') ?: null,
        ];

        $sortBy = $request->get('sortBy', 'id');
        $sortDirection = strtolower($request->get('sortDirection', 'desc')) === 'asc' ? 'asc' : 'desc';
        $groupBy = $request->get('groupBy');

        $canViewGlobal = Auth::user()->can('items.view_global') || $isSuperadmin;
        
        $query = Item::query()
            ->with(['branch', 'category', 'unit'])
            ->select('items.*')
            ->selectSub(function ($query) {
                $query->from('item_stocks')
                    ->selectRaw("COALESCE(SUM(CASE WHEN type IN ('In', 'in', 'adjust_in', 'transfer_in', 'return_in') THEN amount ELSE -amount END), 0)")
                    ->whereColumn('item_stocks.item_id', 'items.id');
            }, 'true_stock')
            ->leftJoin('item_categories as ic', 'ic.id', '=', 'items.category_id')
            ->leftJoin('units as u', 'u.id', '=', 'items.unit_id')
            ->leftJoin('branches as b', 'b.id', '=', 'items.branch_id')
            ->when(!$canViewGlobal && empty($filters['branch_id']), function ($query) use ($userBranchId) {
                return $query->where('items.branch_id', $userBranchId);
            });

        if ($filters['q']) {
            $search = $filters['q'];
            $query->where(function ($w) use ($search) {
                $w->where('items.code', 'like', "%{$search}%")
                    ->orWhere('items.name', 'like', "%{$search}%");
            });
        }
        if ($filters['code']) {
            $query->where('items.code', 'like', "%{$filters['code']}%");
        }
        if ($filters['name']) {
            $query->where('items.name', 'like', "%{$filters['name']}%");
        }
        if ($filters['unit_id']) {
            $query->where('items.unit_id', $filters['unit_id']);
        }
        if ($filters['branch_id']) {
            $query->where('items.branch_id', $filters['branch_id']);
        }
        if ($filters['item_id']) {
            $query->where('items.id', $filters['item_id']);
        }
        if ($filters['category_id']) {
            $query->where('items.category_id', $filters['category_id']);
        }

        // Auto-sort by groupBy field if provided
        if ($groupBy) {
            switch ($groupBy) {
                case 'category':
                    $query->orderBy('ic.name', 'asc');
                    break;
                case 'branch':
                    $query->orderBy('b.name', 'asc');
                    break;
                case 'item':
                    $query->orderBy('items.name', 'asc');
                    break;
            }
        }

        switch ($sortBy) {
            case 'code':
            case 'name':
                $query->orderBy("items.$sortBy", $sortDirection);
                break;
            case 'category':
                $query->orderBy('ic.name', $sortDirection);
                break;
            case 'unit':
            case 'unit_id':
                $query->orderBy('u.name', $sortDirection);
                break;
            case 'branch':
                $query->orderBy('b.name', $sortDirection);
                break;
            default:
                $query->orderBy('items.id', $sortDirection);
        }

        $items = $query->paginate($perPage)->withQueryString();



        return Inertia::render('Admin/Items/Index', [
            'user' => $user,
            'isSuperadmin' => $isSuperadmin,
            'items' => $items->through(function ($gt) {
                return [
                    'id' => $gt->id,
                    'code' => $gt->code,
                    'name' => $gt->name,
                    'image_url' => $gt->image_url,
                    'category' => $gt->category?->name ?? '-',
                    'category_id' => $gt->category_id,
                    'branch_id' => $gt->branch_id,
                    'branch' => $gt->branch?->name ?? null,
                    'stock' => $gt->true_stock ?? 0,
                    'min_stock' => $gt->min_stock ?? 0,
                    'unit' => $gt->unit?->short_name ?? $gt->unit?->name ?? '-',
                    'unit_id' => $gt->unit_id,
                    'last_stock' => $gt->true_stock ?? 0,
                    'created_at' => $gt->created_at->locale('id')->translatedFormat('d F Y'),
                ];
            }),
            'branches' => Branch::get(),
            'categories' => ItemCategory::get(),
            'units' => Unit::get(),
            'filters' => array_merge($filters),
            'sortBy' => $sortBy,
            'sortDirection' => $sortDirection,
            'groupBy' => in_array($groupBy, ['ordered_by'], true) ? $groupBy : null,
            'sidebarCounts' => [
                'total' => $canViewGlobal ? Item::count() : Item::where('branch_id', $userBranchId)->count(),
            ],
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'code' => ['nullable', 'string', 'max:50'],
            'category_id' => ['required', 'exists:item_categories,id'],
            'unit_id' => ['nullable', 'exists:units,id'],
            'description' => ['nullable', 'string'],
            'min_stock' => ['nullable', 'numeric', 'min:0'],
            'image_path' => ['nullable', 'string', 'max:255'],
        ], [
            'category_id.required' => 'Kategori barang wajib di isi.',
            'category_id.exists' => 'Kategori barang tidak valid.',
        ]);

        if (empty($validated['code'])) {
            do {
                $nextNumber = Item::max('id') + 1;
                $generatedCode = 'ITM-' . str_pad($nextNumber, 3, '0', STR_PAD_LEFT);
            } while (Item::where('code', $generatedCode)->exists());
            $validated['code'] = $generatedCode;
        } else {
            // Kode unik divalidasi hanya terhadap barang AKTIF; barang yang
            // sudah diarsipkan (soft delete) tidak mengunci kodenya.
            $duplicate = Item::where('code', $validated['code'])->exists();
            if ($duplicate) {
                return back()->withInput()->with('error', 'Kode barang sudah digunakan oleh barang lain yang masih aktif.');
            }
        }

        $validated['min_stock'] = $validated['min_stock'] ?? 0;

        try {
            DB::transaction(function () use ($validated) {
                $branches = Branch::all();
                foreach ($branches as $branch) {
                    Item::create($validated + ['branch_id' => $branch->id]);
                }
            });

            return back()->with('success', 'Barang berhasil ditambahkan');
        } catch (Throwable $e) {
            Log::error('Store Item failed', ['error' => $e->getMessage()]);
            return back()->with('error', 'Gagal menambah barang: ' . $e->getMessage());
        }
    }

    public function update(Request $request, Item $item)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'code' => ['nullable', 'string'],
            'branch_id' => ['required', 'exists:branches,id'],
            'category_id' => ['required', 'exists:item_categories,id'],
            'unit_id' => ['required', 'exists:units,id'],
            'description' => ['nullable', 'string'],
            'min_stock' => ['nullable', 'numeric', 'min:0'],
            'image_path' => ['nullable', 'string', 'max:255'],
        ], [
            'category_id.required' => 'Kategori barang wajib di isi.',
            'category_id.exists' => 'Kategori barang tidak valid.',
        ]);

        $validated['min_stock'] = $validated['min_stock'] ?? 0;

        try {
            if ($item->code) {
                $items = Item::where('code', $item->code)->get();
            } else {
                $items = Item::where('name', $item->name)->get();
            }

            $updateData = $validated;
            unset($updateData['branch_id']);

            foreach ($items as $row) {
                $row->update($updateData);
            }

            return back()->with('success', 'Barang berhasil diupdate');
        } catch (Throwable $e) {
            Log::error('Update Item failed', ['error' => $e->getMessage()]);
            return back()->with('error', 'Gagal Update barang: ' . $e->getMessage());
        }
    }

    private function deleteItemGroup(string $code): ?bool
    {
        $items = Item::where('code', $code)->get();
        if ($items->isEmpty()) {
            return null;
        }

        $hasHistory = $items->contains(fn ($row) =>
            $row->stocks()->exists() || $row->transfers()->exists() || $row->movements()->exists() ||
            $row->goodIssueItems()->exists() || $row->purchaseRequestItems()->exists() ||
            $row->goodReceiptItems()->exists() || $row->purchaseOrderItems()->exists() ||
            $row->materialRequestItems()->exists()
        );

        DB::transaction(function () use ($items, $hasHistory) {
            foreach ($items as $row) {
                $hasHistory ? $row->delete() : $row->forceDelete();
            }
        });

        return $hasHistory;
    }

    public function destroy(Item $item)
    {
        try {
            $this->deleteItemGroup($item->code);

            return back()->with('success', 'Berhasil menghapus barang');
        } catch (Throwable $e) {
            Log::error('Delete Item failed', ['error' => $e->getMessage()]);
            return back()->with('error', $e->getMessage() ?: 'Gagal menghapus barang');
        }
    }

    public function bulkDestroy(Request $request)
    {
        $validated = $request->validate([
            'codes' => ['required', 'array', 'min:1'],
            'codes.*' => ['string'],
        ]);

        try {
            $deletedCount = 0;

            foreach (array_unique($validated['codes']) as $code) {
                $archived = $this->deleteItemGroup($code);
                if ($archived === null) {
                    continue;
                }
                $deletedCount++;
            }

            return back()->with('success', "Berhasil menghapus {$deletedCount} barang");
        } catch (Throwable $e) {
            Log::error('Bulk delete Item failed', ['error' => $e->getMessage()]);
            return back()->with('error', 'Gagal menghapus barang terpilih: ' . $e->getMessage());
        }
    }

    public function show(Item $item)
    {
        $item->load(['category:id,name', 'unit:id,name,short_name', 'branch:id,name']);

        $stockIn = $item->stocks()->whereMonth('created_at', now()->month)->where('type', 'In')->sum('amount');
        $stockOut = $item->stocks()->whereMonth('created_at', now()->month)->where('type', 'Out')->sum('amount');

        $movements = $item->stocks()->with('item.branch:id,name')->orderByDesc('created_at')->paginate(20)->withQueryString();

        $stocksByBranch = Branch::select('id', 'name')->with([
            'items' => function ($query) use ($item) {
                $query->where('items.code', $item->code)->with('stock');
            }
        ])->get()->map(function ($b) {
            return [
                'branch_id' => $b->id,
                'branch' => $b->name,
                'current_stock' => $b->items->first()?->stock?->last_stock ?? 0,
            ];
        });


        return Inertia::render('Admin/Items/Show', [
            'stockIn' => $stockIn,
            'stockOut' => $stockOut,
            'item' => [
                'id' => $item->id,
                'code' => $item->code,
                'name' => $item->name,
                'category' => $item->category?->name,
                'unit' => $item->unit?->short_name ?? $item->unit?->name,
                'last_stock' => $item->stock?->last_stock ?? 0,
            ],
            'stocksByBranch' => $stocksByBranch,
            'movements' => $movements->through(function ($mv) {
                return [
                    'id' => $mv->id,
                    'at' => $mv->created_at?->toDateTimeString(),
                    'branch' => $mv->item?->branch?->name ?? '-',
                    'type' => $mv->type,
                    'quantity' => (float) $mv->amount,
                    'last_stock' => (float) $mv->last_stock,
                    'current_stock' => (float) $mv->initial_stock,
                    'reference_type' => $mv->source_type ?? null,
                    'reference_id' => $mv->source_id ?? null,
                    'notes' => $mv->note ?? null,
                ];
            }),
        ]);
    }
}
