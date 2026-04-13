<?php

namespace App\Http\Controllers;

use Throwable;
use Carbon\Carbon;
use Inertia\Inertia;
use App\Models\Shift;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Gate;

class ShiftController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        abort_unless(Gate::allows('shifts.view'), 403, 'Anda tidak memiliki akses untuk melihat data shift');

        $sortBy = $request->get('sortBy', 'id');
        $sortDirection = $request->get('sortDirection', 'desc');

        $search = $request->input('search');
        $perPage = $request->input('per_page', 10);

        $shifts = Shift::when($search, function ($query, $search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%");
                if (Schema::hasColumn('shifts', 'code')) {
                    $q->orWhere('code', 'like', "%{$search}%");
                }
            });
        })
            ->orderBy($sortBy, $sortDirection)
            ->paginate($perPage)
            ->withQueryString();

        $shifts->each(function ($item) {
            $start = Carbon::parse($item->start_time);
            $end = Carbon::parse($item->end_time);

            if ($end->lessThan($start)) {
                $end->addDay();
            }

            $minutes = $start->diffInMinutes($end);

            $hours = floor($minutes / 60);
            $remainingMinutes = $minutes % 60;

            if ($remainingMinutes === 0) {
                $item->duration = "{$hours} jam";
            } else {
                $item->duration = "{$hours} jam {$remainingMinutes} menit";
            }
        });

        return Inertia::render('Admin/Shifts/Index', compact('shifts', 'sortBy', 'sortDirection', 'search'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        abort_unless(Gate::allows('shifts.create'), 403, 'Anda tidak memiliki akses untuk menambah shift');

        $rules = [
            'name'           => 'required|string|max:255',
            'start_time'     => 'required|date_format:H:i',
            'end_time'       => 'required|date_format:H:i',
            'late_tolerance' => 'nullable|date_format:H:i',
            'overtime_start' => 'nullable|date_format:H:i',
            'weekly_pattern' => 'nullable|array',
        ];
        if (Schema::hasColumn('shifts', 'code')) {
            $rules['code'] = 'nullable|string|max:20|unique:shifts,code';
        }
        $data = $request->validate($rules);

        // Auto-generate code if not provided
        if (Schema::hasColumn('shifts', 'code')) {
            if (empty($data['code'] ?? null)) {
                $data['code'] = $this->generateNextCode();
            } else {
                $data['code'] = strtoupper($data['code']);
            }
        }

        Shift::create($data);

        return redirect()->back()->with('success', 'Shift berhasil ditambahkan.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Shift $shift)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Shift $shift)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Shift $shift)
    {
        abort_unless(Gate::allows('shifts.edit'), 403, 'Anda tidak memiliki akses untuk mengubah shift');

        $rules = [
            'name' => 'required|string|max:255',
            'start_time' => 'required|date_format:H:i',
            'late_tolerance' => 'nullable|date_format:H:i',
            'end_time' => 'required|date_format:H:i',
            'overtime_start' => 'nullable|date_format:H:i',
            'weekly_pattern' => 'nullable|array',
        ];
        if (Schema::hasColumn('shifts', 'code')) {
            $rules['code'] = 'nullable|string|max:20|unique:shifts,code,' . $shift->id;
        }
        $data = $request->validate($rules);

        if (Schema::hasColumn('shifts', 'code')) {
            if (empty($data['code'] ?? null)) {
                $data['code'] = $shift->code ?: $this->generateNextCode();
            } else {
                $data['code'] = strtoupper($data['code']);
            }
        }

        $shift->update($data);

        return redirect()->back()->with('success', 'Shift berhasil diperbarui.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Shift $shift)
    {
        abort_unless(Gate::allows('shifts.delete'), 403, 'Anda tidak memiliki akses untuk menghapus shift');

        try {
            // Cek apakah shift memiliki relasi dengan attendance_shift_work
            if ($shift->attendanceShiftWorks()->exists()) {
                return redirect()->back()->with('error', 'Data shift tidak dapat dihapus karena masih memiliki relasi dengan data shift kerja');
            }

            $shift->delete();

            return redirect()->back()->with('success', 'Shift berhasil dihapus.');
        } catch (Throwable $e) {
            return redirect()->back()->with('error', 'Terjadi kesalahan saat menghapus shift: ' . $e->getMessage());
        }
    }

    /**
     * Generate next sequential shift code with prefix 'S'
     */
    private function generateNextCode(): string
    {
        // Find max numeric suffix where code matches S<number>
        if (!Schema::hasColumn('shifts', 'code')) {
            return 'S1';
        }

        $max = Shift::where('code', 'like', 'S%')
            ->get(['code'])
            ->map(function ($row) {
                if (preg_match('/^S(\d+)$/i', $row->code, $m)) {
                    return (int) $m[1];
                }
                return 0;
            })
            ->max() ?? 0;

        return 'S' . ($max + 1);
    }
}
