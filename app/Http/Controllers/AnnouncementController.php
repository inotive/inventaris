<?php

namespace App\Http\Controllers;

use App\Models\Announcement;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Gate;

class AnnouncementController extends Controller
{
    public function index(Request $request)
    {
        abort_unless(Gate::allows('announcements.view'), 403, 'Anda tidak memiliki akses untuk melihat data pengumuman');

        $q = $request->input('q');
        $announcements = Announcement::query()
            ->with(['user:id,name'])
            ->when($q, function ($s) use ($q) {
                $s->where('title', 'like', "%{$q}%");
            })
            ->orderByDesc('created_at')
            ->paginate($request->input('per_page', 10))
            ->withQueryString();

        return Inertia::render('Admin/Announcements/Index', [
            'announcements' => $announcements,
            'q' => $q,
        ]);
    }

    public function store(Request $request)
    {
        abort_unless(Gate::allows('announcements.create'), 403, 'Anda tidak memiliki akses untuk menambah pengumuman');

        $data = $request->validate([
            'title' => 'required|string|max:255',
            'created_by' => 'nullable|integer',
            'img_file' => 'required|image|max:10240',
            'content' => 'required|string',
        ], [
            'title.required' => 'Judul pengumuman wajib diisi.',
            'title.string' => 'Judul pengumuman harus berupa teks.',
            'title.max' => 'Judul pengumuman tidak boleh lebih dari 255 karakter.',
            'created_by.integer' => 'ID pembuat pengumuman harus berupa angka.',
            'img_file.image' => 'File gambar harus berupa gambar yang valid (jpg, png, dan sejenisnya).',
            'img_file.max' => 'Ukuran file gambar tidak boleh lebih dari 10 MB.',
            'content.string' => 'Isi pengumuman harus berupa teks.',
        ]);

        if (empty($data['created_by']) && auth()->check()) {
            $data['created_by'] = auth()->id();
        }

        // Ensure content key exists to avoid NOT NULL DB constraint
        if (!array_key_exists('content', $data)) {
            $data['content'] = (string) $request->input('content', '');
        }

        if ($request->hasFile('img_file')) {
            $path = $request->file('img_file')->store('announcements', 'public');
            $data['img_url'] = Storage::url($path);
        }

        Announcement::create($data);

        return redirect()->route('announcements.index')->with('success', 'Pengumuman berhasil ditambahkan');
    }

    public function update(Request $request, Announcement $announcement)
    {
        abort_unless(Gate::allows('announcements.edit'), 403, 'Anda tidak memiliki akses untuk mengubah pengumuman');

        $data = $request->validate([
            'title' => 'required|string|max:255',
            'img_file' => 'required|image|max:10240',
            'content' => 'required|string',
        ], [
            'title.required' => 'Judul pengumuman wajib diisi.',
            'title.string' => 'Judul pengumuman harus berupa teks.',
            'title.max' => 'Judul pengumuman tidak boleh lebih dari 255 karakter.',
            'img_file.image' => 'File gambar harus berupa gambar yang valid (jpg, png, dan sejenisnya).',
            'img_file.max' => 'Ukuran file gambar tidak boleh lebih dari 10 MB.',
            'content.string' => 'Isi pengumuman harus berupa teks.',
        ]);

        if (!array_key_exists('content', $data)) {
            $data['content'] = (string) $request->input('content', '');
        }

        if ($request->hasFile('img_file')) {
            $path = $request->file('img_file')->store('announcements', 'public');
            $data['img_url'] = Storage::url($path);
        }

        $announcement->update($data);

        return redirect()->route('announcements.index')->with('success', 'Pengumuman berhasil diperbarui');
    }

    public function destroy(Announcement $announcement)
    {
        abort_unless(Gate::allows('announcements.delete'), 403, 'Anda tidak memiliki akses untuk menghapus pengumuman');

        $announcement->delete();

        return redirect()->back()->with('success', 'Pengumuman berhasil dihapus');
    }
}
