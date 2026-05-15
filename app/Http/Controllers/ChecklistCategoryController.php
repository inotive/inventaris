<?php

namespace App\Http\Controllers;

use App\Models\ChecklistCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;

class ChecklistCategoryController extends Controller
{
    public function index(Request $request)
    {
        abort_unless(Gate::allows('checklist_categories.view'), 403, 'Anda tidak memiliki akses untuk melihat kategori checklist');

        $sortBy        = $request->get('sortBy', 'name');
        $sortDirection = $request->get('sortDirection', 'asc');
        $search        = $request->input('search');
        $perPage       = (int) $request->input('per_page', 10);

        // Whitelist sortable fields
        $allowedSorts = ['id', 'name', 'code', 'description'];
        if (!in_array($sortBy, $allowedSorts, true)) {
            $sortBy = 'name';
        }
        $sortDirection = strtolower($sortDirection) === 'desc' ? 'desc' : 'asc';

        $categories = ChecklistCategory::when($search, function ($query, $search) {
                $query->where(function ($w) use ($search) {
                    $w->where('name', 'like', "%{$search}%")
                      ->orWhere('code', 'like', "%{$search}%")
                      ->orWhere('description', 'like', "%{$search}%");
                });
            })
            ->orderBy($sortBy, $sortDirection)
            ->paginate($perPage)
            ->withQueryString();

        return Inertia::render('Admin/Checklist/Categories/Index', compact('categories', 'sortBy', 'sortDirection', 'search'));
    }

    public function create()
    {
        abort_unless(Gate::allows('checklist_categories.create'), 403, 'Anda tidak memiliki akses untuk menambah kategori checklist');

        return Inertia::render('Admin/Checklist/Categories/Create');
    }

    public function edit(ChecklistCategory $checklist_category)
    {
        abort_unless(Gate::allows('checklist_categories.edit'), 403, 'Anda tidak memiliki akses untuk mengubah kategori checklist');

        return Inertia::render('Admin/Checklist/Categories/Edit', [
            'category' => [
                'id'          => $checklist_category->id,
                'name'        => $checklist_category->name,
                'code'        => $checklist_category->code,
                'description' => $checklist_category->description,
            ],
        ]);
    }

    public function store(Request $request)
    {
        abort_unless(Gate::allows('checklist_categories.create'), 403, 'Anda tidak memiliki akses untuk menambah kategori checklist');

        $validated = $request->validate([
            'name'        => ['required', 'unique:checklist_categories,name', 'string', 'max:100'],
            'code'        => ['required', 'string', 'max:50', 'unique:checklist_categories,code'],
            'description' => ['nullable', 'string'],
        ], [
            'name.required' => 'Nama kategori harus diisi.',
            'name.unique' => 'Nama kategori sudah digunakan.',
            'name.string' => 'Nama kategori harus berupa teks.',
            'name.max' => 'Nama kategori maksimal :max karakter.',
            'code.required' => 'Kode kategori harus diisi.',
            'code.unique' => 'Kode kategori sudah digunakan.',
            'code.string' => 'Kode kategori harus berupa teks.',
            'code.max' => 'Kode kategori maksimal :max karakter.',
            'description.string' => 'Deskripsi harus berupa teks.',
        ]);

        try {
            DB::transaction(function () use ($validated) {
                ChecklistCategory::create($validated);
            });
            return redirect()->route('checklist-categories.index')->with('success', 'Kategori checklist berhasil ditambahkan');
        } catch (\Throwable $e) {
            Log::error('Store ChecklistCategory failed', ['error' => $e->getMessage()]);
            return back()->with('error', 'Gagal menambah kategori checklist');
        }
    }

    public function update(Request $request, ChecklistCategory $checklist_category)
    {
        abort_unless(Gate::allows('checklist_categories.edit'), 403, 'Anda tidak memiliki akses untuk mengubah kategori checklist');

        $validated = $request->validate([
            'name'        => ['required', 'string', 'max:100'],
            'code'        => ['required', 'string', 'max:50', 'unique:checklist_categories,code,' . $checklist_category->id],
            'description' => ['nullable', 'string'],
        ]);

        try {
            DB::transaction(function () use ($checklist_category, $validated) {
                $checklist_category->update($validated);
            });
            return redirect()->route('checklist-categories.index')->with('success', 'Kategori checklist berhasil diubah');
        } catch (\Throwable $e) {
            Log::error('Update ChecklistCategory failed', ['error' => $e->getMessage()]);
            return back()->with('error', 'Gagal mengubah kategori checklist');
        }
    }

    public function destroy(ChecklistCategory $checklist_category)
    {
        abort_unless(Gate::allows('checklist_categories.delete'), 403, 'Anda tidak memiliki akses untuk menghapus kategori checklist');

        // Validasi: Cek apakah kategori masih digunakan di modul Checklist
        $checklistCount = $checklist_category->checklists()->count();
        if ($checklistCount > 0) {
            return back()->with('error', "Kategori checklist tidak dapat dihapus karena masih digunakan pada {$checklistCount} checklist. Hapus atau ubah checklist terkait terlebih dahulu.");
        }

        try {
            DB::transaction(function () use ($checklist_category) {
                $checklist_category->delete();
            });
            return back()->with('success', 'Kategori checklist berhasil dihapus');
        } catch (\Throwable $e) {
            Log::error('Delete ChecklistCategory failed', ['error' => $e->getMessage()]);
            return back()->with('error', 'Gagal menghapus kategori checklist');
        }
    }
}
