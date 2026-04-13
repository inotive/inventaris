<?php

namespace App\Http\Controllers;

use Throwable;
use Inertia\Inertia;
use App\Models\ItemCategory;
use App\Models\Item;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Gate;

class ItemCategoryController extends Controller
{
    public function index(Request $request)
    {
        abort_unless(Gate::allows('item_categories.view'), 403, 'Anda tidak memiliki akses untuk melihat kategori barang');

        // Default to latest updated first
        $sortBy = $request->get('sortBy', 'updated_at');
        $sortDirection = $request->get('sortDirection', 'desc');

        $search  = $request->input('search');
        $perPage = $request->input('per_page', 10);

        $itemCategories = ItemCategory::when($search, function ($query, $search) {
                $query->where('name', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            })
            ->orderBy($sortBy, $sortDirection)
            ->paginate($perPage)
            ->withQueryString();

        return Inertia::render('Admin/ItemCategories/Index', compact(
            'itemCategories',
            'sortBy',
            'sortDirection',
            'search'
        ));
    }

    public function store(Request $request)
    {
        abort_unless(Gate::allows('item_categories.create'), 403, 'Anda tidak memiliki akses untuk menambah kategori barang');

        $validated = $request->validate([
            'name'        => ['required', 'string', 'max:150'],
            'description' => ['nullable', 'string'],
        ]);

        try {
            DB::transaction(fn () => ItemCategory::create($validated));
            return back()->with('success', 'Kategori barang berhasil ditambahkan');
        } catch (Throwable $e) {
            Log::error('Gagal menambah kategori barang', ['error' => $e->getMessage()]);
            return back()->with('error', 'Gagal menambah kategori barang');
        }
    }

    public function update(Request $request, ItemCategory $itemCategory)
    {
        abort_unless(Gate::allows('item_categories.edit'), 403, 'Anda tidak memiliki akses untuk mengubah kategori barang');

        $validated = $request->validate([
            'name'        => ['required', 'string', 'max:150'],
            'description' => ['nullable', 'string'],
        ]);

        try {
            $itemCategory->update($validated);
            return back()->with('success', 'Kategori barang berhasil diubah');
        } catch (Throwable $e) {
            Log::error('Gagal mengubah kategori barang', ['error' => $e->getMessage()]);
            return back()->with('error', 'Gagal mengubah kategori barang');
        }
    }

    public function destroy(ItemCategory $itemCategory)
    {
        abort_unless(Gate::allows('item_categories.delete'), 403, 'Anda tidak memiliki akses untuk menghapus kategori barang');

        // Check if category is being used by any items
        $itemsCount = Item::where('category_id', $itemCategory->id)->count();

        if ($itemsCount > 0) {
            return back()->with('error', 'Gagal Menghapus, Data sedang digunakan');
        }

        try {
            DB::transaction(fn () => $itemCategory->delete());
            return back()->with('success', 'Kategori barang berhasil dihapus!');
        } catch (Throwable $e) {
            Log::error('Gagal menghapus kategori barang.', ['error' => $e->getMessage()]);
            return back()->with('error', 'Gagal menghapus kategori barang.');
        }
    }
}
