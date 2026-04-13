<?php

namespace App\Http\Controllers;

use Throwable;
use Inertia\Inertia;
use App\Models\Branch;
use App\Models\AbsenceArea;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Gate;

class AbsenceAreaController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        abort_unless(Gate::allows('absence_areas.view'), 403, 'Anda tidak memiliki akses untuk melihat data wilayah absen');

        $sortBy        = $request->get('sortBy', 'id');
        $sortDirection = $request->get('sortDirection', 'asc');

        $search  = $request->input('search');
        $perPage = $request->input('per_page', 10);

        $areas = AbsenceArea::with(['branch'])
            ->when($search, function ($query, $search) {
                $query->where('name', 'like', "%{$search}%")
                    ->orWhereHas('branch', function ($q) use ($search) {
                        $q->where('name', 'like', "%{$search}%");
                    });
            })
            ->orderBy($sortBy, $sortDirection)
            ->paginate($perPage)
            ->withQueryString();

        $branches = Branch::orderBy('name')->get(['id', 'name']);

        return Inertia::render('Admin/AbsenceAreas/Index', compact(
            'areas',
            'branches',
            'search',
            'sortBy',
            'sortDirection',
            'perPage'
        ));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        abort_unless(Gate::allows('absence_areas.create'), 403, 'Anda tidak memiliki akses untuk menambah wilayah absen');

        $data = $request->validate([
            'name'      => ['required', 'unique:absence_areas,name'],
            'branch_id' => ['nullable', 'exists:branches,id'],
            'latitude'  => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180'],
        ]);

        try {
            DB::transaction(function () use ($data) {
                AbsenceArea::create([
                    'name'      => $data['name'],
                    'branch_id' => $data['branch_id'] ?? null,
                    'latitude'  => $data['latitude'],
                    'longitude' => $data['longitude'],
                ]);
            });

            return redirect()->back()->with('success', 'Wilayah absen berhasil ditambahkan.');
        } catch (Throwable $e) {
            Log::error('Gagal menambah wilayah absen', ['err' => $e->getMessage()]);

            return redirect()->back()->with('error', 'Gagal menambah wilayah absen.');
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, AbsenceArea $absenceArea)
    {
        abort_unless(Gate::allows('absence_areas.edit'), 403, 'Anda tidak memiliki akses untuk mengubah wilayah absen');

        $data = $request->validate([
            'name'      => 'required|unique:absence_areas,name,' . $absenceArea->id,
            'branch_id' => 'nullable|exists:branches,id',
            'latitude'  => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
        ]);

        try {
            DB::transaction(function () use ($data, $absenceArea) {
                $absenceArea->update($data);
            });

            return redirect()->back()->with('success', 'Wilayah absen berhasil diperbarui.');
        } catch (Throwable $e) {
            Log::error('Gagal memperbarui wilayah absen', ['err' => $e->getMessage()]);

            return redirect()->back()->with('error', 'Gagal memperbarui wilayah absen.');
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(AbsenceArea $absenceArea)
    {
        abort_unless(Gate::allows('absence_areas.delete'), 403, 'Anda tidak memiliki akses untuk menghapus wilayah absen');

        try {
            // if ($absenceArea->absence()->exists()) {
            //     return redirect()->back()->with('error', 'Wilayah memiliki data absensi.');
            // }

            DB::transaction(function () use ($absenceArea) {
                $absenceArea->delete();
            });

            return redirect()->back()->with('success', 'Wilayah absen berhasil dihapus');
        } catch (Throwable $e) {
            Log::error('Gagal menghapus wilayah absen.', ['err' => $e->getMessage()]);
            return redirect()->back()->with('error', 'Gagal menghapus wilayah absen.');
        }
    }
}
