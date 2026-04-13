<?php

namespace App\Http\Controllers;

use App\Models\Compliance;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class ComplianceController extends Controller
{

    public function index(Request $request)
    {
        abort_unless(Gate::allows('compliance.view'), 403, 'Anda tidak memiliki akses untuk melihat data compliance');

        $q = $request->input('q');
        $perPage = (int) $request->input('per_page', 10);
        $rows = Compliance::query()
            ->with(['user:id,name'])
            ->when($q, function ($s) use ($q) {
                $s->where('title', 'like', "%{$q}%");
            })
            ->orderByDesc('created_at')
            ->paginate($perPage)
            ->withQueryString();

        return Inertia::render('Admin/Compliance/Index', [
            'compliances' => $rows,
            'q' => $q,
            'perPage' => $perPage,
        ]);
    }

    public function store(Request $request)
    {
        abort_unless(Gate::allows('compliance.create'), 403, 'Anda tidak memiliki akses untuk menambah compliance');

        $data = $request->validate([
            'section' => 'required|in:hak,larangan',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'created_by' => 'nullable|integer',
        ]);
        if (empty($data['created_by']) && Auth::check()) {
            $data['created_by'] = Auth::id();
        }
        if ($request->hasFile('img_file')) {
            $path = $request->file('img_file')->store('compliances', 'public');
            $data['img_url'] = Storage::url($path);
        }
        Compliance::create($data);
        return redirect()->route('compliance.index')->with('success', 'Data berhasil ditambahkan');
    }

    public function update(Request $request, Compliance $compliance)
    {
        abort_unless(Gate::allows('compliance.edit'), 403, 'Anda tidak memiliki akses untuk mengubah compliance');

        try {
            $data = $request->validate([
                'section' => 'required|in:hak,larangan',
                'title' => 'required|string|max:255',
                'description' => 'nullable|string',
                'created_by' => 'nullable|integer',
            ]);
            if (empty($data['created_by']) && Auth::check()) {
                $data['created_by'] = Auth::id();
            }
            if ($request->hasFile('img_file')) {
                $path = $request->file('img_file')->store('compliances', 'public');
                $data['img_url'] = Storage::url($path);
            }
            $compliance->update($data);
            return redirect()->route('compliance.index')->with('success', 'Data berhasil diperbarui');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Terjadi kesalahan saat memperbarui data: ' . $e->getMessage());
        }
    }

    public function destroy(Compliance $compliance)
    {
        abort_unless(Gate::allows('compliance.delete'), 403, 'Anda tidak memiliki akses untuk menghapus compliance');

        $compliance->delete();
        return redirect()->back()->with('success', 'Data berhasil dihapus');
    }
}
