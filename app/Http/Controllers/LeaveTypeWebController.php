<?php

namespace App\Http\Controllers;

use App\Models\LeaveType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;

class LeaveTypeWebController extends Controller
{
    public function index(Request $request)
    {
        abort_unless(Gate::allows('leave_types.view'), 403, 'Anda tidak memiliki akses untuk melihat data jenis izin');
        $search = $request->get('search');
        $category = $request->get('category');
        $perPage = (int) $request->input('per_page', 10);
        $leaveTypes = LeaveType::query()
            ->when($search, function ($q) use ($search) {
                $q->where(function ($w) use ($search) {
                    $w->where('name', 'like', "%{$search}%")
                      ->orWhere('description', 'like', "%{$search}%");
                });
            })
            ->when($category, function ($q) use ($category) {
                $q->where('category', $category);
            })
            ->orderBy('name')
            ->paginate($perPage)
            ->withQueryString();

        return Inertia::render('Admin/LeaveTypes/Index', [
            'leaveTypes' => $leaveTypes,
            'filters' => [
                'search' => $search,
                'category' => $category,
            ],
        ]);
    }

    public function store(Request $request)
    {
        abort_unless(Gate::allows('leave_types.create'), 403, 'Anda tidak memiliki akses untuk menambahkan data jenis izin');
        $data = $request->validate([
            'name' => ['required','string','max:255'],
            'category' => ['required','in:annual_leave,sick_leave,special_leave'],
            'leave_quota_per_year' => ['required','integer','min:0'],
            'description' => ['nullable','string'],
        ]);

        LeaveType::create($data);
        return redirect()->route('leave-types.index')->with('success', 'Kategori izin berhasil dibuat');
    }

    public function update(Request $request, LeaveType $leave_type)
    {
        abort_unless(Gate::allows('leave_types.edit'), 403, 'Anda tidak memiliki akses untuk mengedit data jenis izin');
        $data = $request->validate([
            'name' => ['required','string','max:255'],
            'category' => ['required','in:annual_leave,sick_leave,special_leave'],
            'leave_quota_per_year' => ['required','integer','min:0'],
            'description' => ['nullable','string'],
        ]);

        $leave_type->update($data);
        return redirect()->route('leave-types.index')->with('success', 'Kategori izin berhasil diperbarui');
    }

    public function destroy(LeaveType $leave_type)
    {
        abort_unless(Gate::allows('leave_types.delete'), 403, 'Anda tidak memiliki akses untuk menghapus data jenis izin');

        // Cek apakah ada relasi dengan leave requests
        if ($leave_type->leaveRequests()->exists()) {
            return redirect()->route('leave-types.index')
                ->with('error', 'Jenis izin tidak bisa dihapus karena masih digunakan pada permintaan cuti karyawan.');
        }

        // Cek apakah ada relasi dengan leave balances
        if ($leave_type->leaveBalances()->exists()) {
            return redirect()->route('leave-types.index')
                ->with('error', 'Jenis izin tidak bisa dihapus karena masih digunakan pada kuota cuti karyawan.');
        }

        $leave_type->delete();
        return redirect()->route('leave-types.index')->with('success', 'Kategori izin dihapus');
    }
}
