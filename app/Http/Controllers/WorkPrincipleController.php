<?php

namespace App\Http\Controllers;

use App\Models\WorkPrinciple;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Gate;

class WorkPrincipleController extends Controller
{
    public function index(Request $request)
    {
        abort_unless(Gate::allows('work_principles.view'), 403, 'Anda tidak memiliki akses untuk melihat data prinsip & etos kerja');

        $q = $request->input('q');
        $perPage = (int) $request->input('per_page', 10);
        $category = $request->input('category');
        $rows = WorkPrinciple::query()
            ->when($q, function ($s) use ($q) {
                $s->where(function ($w) use ($q) {
                    $w->where('title', 'like', "%{$q}%")
                        ->orWhere('description', 'like', "%{$q}%")
                        ->orWhere('category', 'like', "%{$q}%");
                });
            })
            ->when($category, function ($s) use ($category) {
                $s->where('category', $category);
            })
            ->orderByDesc('created_at')
            ->paginate($perPage)
            ->withQueryString();

        return Inertia::render('Admin/WorkPrinciples/Index', [
            'rows' => $rows,
            'filters' => [
                'q' => $q,
                'category' => $category,
            ],
        ]);
    }

    public function store(Request $request)
    {
        abort_unless(Gate::allows('work_principles.create'), 403, 'Anda tidak memiliki akses untuk menambah prinsip & etos kerja');

        $data = $request->validate([
            'category' => 'required|in:prinsip,etos kerja',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'created_by' => 'nullable|integer',
            'img_file' => 'nullable|image|max:2048',
        ]);

        // set creator automatically when available
        if (empty($data['created_by']) && auth()->check()) {
            $data['created_by'] = auth()->id();
        }

        if ($request->hasFile('img_file')) {
            $path = $request->file('img_file')->store('work_principles', 'public');
            $data['img_url'] = Storage::url($path);
        }

        WorkPrinciple::create($data);

        return redirect()->route('work-principles.index')->with('success', 'Data berhasil ditambahkan');
    }

    public function update(Request $request, WorkPrinciple $work_principle)
    {
        abort_unless(Gate::allows('work_principles.edit'), 403, 'Anda tidak memiliki akses untuk mengubah prinsip & etos kerja');

        $data = $request->validate([
            'category' => 'required|in:prinsip,etos kerja',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'created_by' => 'nullable|integer',
            'img_file' => 'nullable|image|max:2048',
        ]);

        if (empty($data['created_by']) && auth()->check()) {
            $data['created_by'] = auth()->id();
        }
        $data['img_url'] = null;

        if ($request->hasFile('img_file')) {
            $path = $request->file('img_file')->store('work_principles', 'public');
            $data['img_url'] = Storage::url($path);
        }

        $work_principle->update($data);

        return redirect()->route('work-principles.index')->with('success', 'Data berhasil diperbarui');
    }

    public function destroy(WorkPrinciple $work_principle)
    {
        abort_unless(Gate::allows('work_principles.delete'), 403, 'Anda tidak memiliki akses untuk menghapus prinsip & etos kerja');

        $work_principle->delete();

        return redirect()->back()->with('success', 'Data berhasil dihapus');
    }
}
