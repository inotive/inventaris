<?php

namespace App\Http\Controllers;

use App\Models\Report;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        // konsisten dgn Shift
        $sortBy        = $request->get('sort_by', 'created_at');
        $sortDirection = $request->get('sort_direction', 'desc');
        $isSuperadmin = Auth::user()->hasRole("Superadmin");

        $search   = $request->input('search');
        $perPage  = (int) $request->input('per_page', 10);
        $year     = $request->integer('year');          // hanya tahun
        $groupBy  = $request->input('groupBy', 'none'); // 'none' | 'judul'

        // Filter baru
        $employeeId   = $request->input('employee_id');
        $departmentId = $request->input('department_id');
        $branchId     = $request->input('branch_id');
        $dateFrom     = $request->input('date_from');
        $dateTo       = $request->input('date_to');

        $allowedSorts = ['id', 'title', 'created_at'];
        if (!in_array($sortBy, $allowedSorts, true)) {
            $sortBy = 'created_at';
        }
        $sortDirection = strtolower($sortDirection) === 'asc' ? 'asc' : 'desc';

        $reports = Report::with('user.employee.department', 'user.employee.branch')
            ->when($search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('title', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%");
                });
            })
            ->when(!$isSuperadmin && Auth::user()->employee->branch_id != 2, function ($qb) {
                $qb->whereHas('user', function ($q) {
                    $q->whereHas('employee', function ($q) {
                        $q->where('branch_id', Auth::user()->employee->branch_id);
                    });
                });
            })
            ->when($year, fn($q) => $q->whereYear('created_at', $year))
            ->when($employeeId, function ($q) use ($employeeId) {
                $q->whereHas('user.employee', fn($query) => $query->where('id', $employeeId));
            })
            ->when($departmentId, function ($q) use ($departmentId) {
                $q->whereHas('user.employee', fn($query) => $query->where('department_id', $departmentId));
            })
            ->when($branchId, function ($q) use ($branchId) {
                $q->whereHas('user.employee', fn($query) => $query->where('branch_id', $branchId));
            })
            ->when($dateFrom, fn($q) => $q->whereDate('created_at', '>=', $dateFrom))
            ->when($dateTo, fn($q) => $q->whereDate('created_at', '<=', $dateTo))
            ->orderBy($sortBy, $sortDirection)
            ->paginate($perPage)
            ->withQueryString();

        // daftar tahun untuk filter
        $years = Report::select(DB::raw('YEAR(created_at) as year'))
            ->distinct()
            ->orderBy('year', 'desc')
            ->pluck('year');

        // Data untuk dropdown filter
        $employees = \App\Models\Employee::select('id', 'name')
            ->when(!$isSuperadmin && Auth::user()->employee->branch_id != 2, function ($q) {
                $q->where('branch_id', Auth::user()->employee->branch_id);
            })
            ->orderBy('name')
            ->get();

        $departments = \App\Models\Department::select('id', 'name')
            ->orderBy('name')
            ->get();

        $branches = \App\Models\Branch::select('id', 'name')
            ->orderBy('name')
            ->get();

        return Inertia::render('Admin/Reports/Index', [
            'reports'        => $reports,
            'sort_by'        => $sortBy,
            'sort_direction' => $sortDirection,
            'search'         => $search,
            'filters'        => [
                'search'        => $search,
                'per_page'      => $perPage,
                'year'          => $year,
                'groupBy'       => $groupBy,
                'employee_id'   => $employeeId,
                'department_id' => $departmentId,
                'branch_id'     => $branchId,
                'date_from'     => $dateFrom,
                'date_to'       => $dateTo,
            ],
            'years'          => $years,
            'employees'      => $employees,
            'departments'    => $departments,
            'branches'       => $branches,
        ]);
    }

    public function create()
    {
        return Inertia::render('Admin/Reports/Create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'title'       => 'required|string|max:255',
            'description' => 'nullable|string',
            'image'       => 'nullable|image|mimes:jpeg,jpg,png|max:4096',
        ], [
            'required'    => ':attribute wajib diisi.',
            'string'      => ':attribute harus berupa teks.',
            'max.string'  => ':attribute maksimal :max karakter.',
            'image'       => 'File :attribute harus berupa gambar.',
            'mimes'       => ':attribute harus berformat: :values.',
            'max.file'    => 'Ukuran :attribute maksimal :max KB.',
        ], [
            'title'       => 'Judul',
            'description' => 'Deskripsi',
            'image'       => 'Gambar',
        ]);

        try {
            $imagePath = null;

            if ($request->hasFile('image')) {
                $file     = $request->file('image');
                $fileName = time() . '_' . $file->getClientOriginalName();
                $imagePath = $file->storeAs('uploads/reports', $fileName, 'public');
            }

            Report::create([
                'title'       => $data['title'],
                'description' => $data['description'] ?? null,
                'image'       => $imagePath,
                'user_id'     => auth()->id(),
            ]);

            return redirect()->route('reports.index')->with('success', 'Laporan berhasil ditambahkan!');
        } catch (\Throwable $e) {
            return back()->withErrors(['error' => 'Gagal menambahkan laporan: ' . $e->getMessage()])->withInput();
        }
    }

    public function show(Report $report)
    {
        $report->load('user');

        return Inertia::render('Admin/Reports/Show', ['report' => $report]);
    }

    public function edit(Report $report)
    {
        return Inertia::render('Admin/Reports/Edit', ['report' => $report]);
    }

    public function update(Request $request, Report $report)
    {
        $data = $request->validate([
            'title'       => 'required|string|max:255',
            'description' => 'nullable|string',
            'image'       => 'nullable|image|mimes:jpeg,jpg,png|max:4096',
        ], [
            'required'    => ':attribute wajib diisi.',
            'string'      => ':attribute harus berupa teks.',
            'max.string'  => ':attribute maksimal :max karakter.',
            'image'       => 'File :attribute harus berupa gambar.',
            'mimes'       => ':attribute harus berformat: :values.',
            'max.file'    => 'Ukuran :attribute maksimal :max KB.',
        ], [
            'title'       => 'Judul',
            'description' => 'Deskripsi',
            'image'       => 'Gambar',
        ]);

        try {
            $imagePath = $report->getRawOriginal('image');

            if ($request->hasFile('image')) {
                if ($imagePath && \Storage::disk('public')->exists($imagePath)) {
                    \Storage::disk('public')->delete($imagePath);
                }
                $file     = $request->file('image');
                $fileName = time() . '_' . $file->getClientOriginalName();
                $imagePath = $file->storeAs('uploads/reports', $fileName, 'public');
            }

            $report->update([
                'title'       => $data['title'],
                'description' => $data['description'] ?? null,
                'image'       => $imagePath,
            ]);

            return redirect()->route('reports.index')->with('success', 'Laporan berhasil diupdate!');
        } catch (\Throwable $e) {
            return back()->withErrors(['error' => 'Gagal mengupdate laporan: ' . $e->getMessage()])->withInput();
        }
    }

    public function destroy(Report $report)
    {
        try {
            $imagePath = $report->getRawOriginal('image');
            if ($imagePath && \Storage::disk('public')->exists($imagePath)) {
                \Storage::disk('public')->delete($imagePath);
            }
            $report->delete();

            return redirect()->route('reports.index')->with('success', 'Laporan berhasil dihapus!');
        } catch (\Throwable $e) {
            return back()->withErrors(['error' => 'Gagal menghapus laporan: ' . $e->getMessage()]);
        }
    }
}
