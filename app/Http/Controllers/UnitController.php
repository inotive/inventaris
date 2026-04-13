<?php

namespace App\Http\Controllers;

use App\Models\Unit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;

class UnitController extends Controller
{
    public function index(Request $request)
    {
        abort_unless(Gate::allows('units.view'), 403, 'Anda tidak memiliki akses untuk melihat data satuan');

        $sortBy        = $request->get('sortBy', 'id');
        $sortDirection = $request->get('sortDirection', 'desc');

        $search  = $request->input('search');
        $perPage = (int) $request->input('per_page', 10);

        $units = Unit::when($search, function ($query, $search) {
                $query->where('name', 'like', "%{$search}%")
                      ->orWhere('short_name', 'like', "%{$search}%");
            })
            ->orderBy($sortBy, $sortDirection)
            ->paginate($perPage)
            ->withQueryString();

        return Inertia::render('Admin/Units/Index', compact('units', 'search', 'sortBy', 'sortDirection', 'perPage'));
    }

    public function store(Request $request)
    {
        abort_unless(Gate::allows('units.create'), 403, 'Anda tidak memiliki akses untuk menambah satuan');

        $validated = $request->validate([
            'name'       => ['required', 'string', 'max:100', 'unique:units,name'],
            'short_name' => ['nullable', 'string', 'max:10', 'unique:units,short_name'],
        ]);

        try {
            DB::transaction(fn () => Unit::create($validated));
            return back()->with('success', 'Satuan berhasil ditambahkan');
        } catch (\Throwable $e) {
            Log::error('Store Unit failed', ['error' => $e->getMessage()]);
            return back()->with('error', 'Gagal menambah satuan');
        }
    }

    public function update(Request $request, Unit $unit)
    {
        abort_unless(Gate::allows('units.edit'), 403, 'Anda tidak memiliki akses untuk mengubah satuan');

        $validated = $request->validate([
            'name'       => ['required', 'string', 'max:100'],
            'short_name' => ['nullable', 'string', 'max:10'],
        ]);

        try {
            DB::transaction(fn () => $unit->update($validated));
            return back()->with('success', 'Satuan berhasil diubah');
        } catch (\Throwable $e) {
            Log::error('Update Unit failed', ['error' => $e->getMessage()]);
            return back()->with('error', 'Gagal mengubah satuan');
        }
    }

    public function destroy(Unit $unit)
    {
        abort_unless(Gate::allows('units.delete'), 403, 'Anda tidak memiliki akses untuk menghapus satuan');

        if ($unit->items()->exists()) {
            return back()->with('error', 'Gagal Menghapus, Data sedang digunakan');
        }

        try {
            DB::transaction(fn () => $unit->delete());
            return back()->with('success', 'Satuan berhasil dihapus');
        } catch (\Throwable $e) {
            Log::error('Delete Unit failed', ['error' => $e->getMessage()]);
            return back()->with('error', 'Gagal menghapus satuan');
        }
    }
}
