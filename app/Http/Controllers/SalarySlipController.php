<?php

namespace App\Http\Controllers;

use App\Models\SalarySlip;
use App\Models\Employee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Inertia\Inertia;

class SalarySlipController extends Controller
{
    public function index(Request $request)
    {
        $q = $request->string('q')->toString();
        $month = $request->input('month');
        $employeeId = $request->integer('employee_id') ?: null;

        $user = Auth::user();
        $isSuperadmin = $user->hasRole('Superadmin');
        $userBranchId = $user->employee->branch_id ?? null;

        $query = SalarySlip::query()->with(['employee:id,name,department_id'])
            ->orderByDesc('bulan')->orderByDesc('id');

        if (!$isSuperadmin && $userBranchId && $userBranchId != 2) {
            $query->whereHas('employee', function ($q) use ($userBranchId) {
                $q->where('branch_id', $userBranchId);
            });
        }

        if ($q) {
            $query->whereHas('employee', function ($s) use ($q) {
                $s->where('name', 'like', "%{$q}%");
            });
        }
        if ($employeeId) $query->where('employee_id', $employeeId);
        if ($month) $query->where('bulan', $month);

        $rows = $query->paginate(15)->withQueryString();

        return Inertia::render('Admin/SalarySlips/Index', [
            'rows' => $rows->through(function ($r) {
                return [
                    'id' => $r->id,
                    'bulan' => $r->bulan,
                    'employee' => ['id' => $r->employee->id ?? null, 'name' => $r->employee->name ?? '-'],
                    'file_url' => $r->file_url,
                    'uploaded_by' => $r->upload_by,
                    'created_at' => $r->created_at,
                ];
            }),
            'employees' => Employee::select('id', 'name')->orderBy('name')
                ->when(!$isSuperadmin && $userBranchId && $userBranchId != 2, function ($query) use ($userBranchId) {
                    $query->where('branch_id', $userBranchId);
                })
                ->limit(500)->get(),
            'filters' => ['q' => $q, 'month' => $month, 'employee_id' => $employeeId],
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'bulan' => 'required|string|size:7', // YYYY-MM format
            'file' => 'required|file|mimes:pdf|max:10240', // Max 10MB
        ]);

        $user = Auth::user();
        $isSuperadmin = $user->hasRole('Superadmin');
        $userBranchId = $user->employee->branch_id ?? null;

        // Security check: ensure employee belongs to user's branch if not Superadmin/Branch 2
        if (!$isSuperadmin && $userBranchId && $userBranchId != 2) {
            $employee = Employee::find($request->employee_id);
            if (!$employee || $employee->branch_id != $userBranchId) {
                return redirect()->back()->with('error', 'Anda tidak memiliki akses untuk menambah slip gaji karyawan ini.');
            }
        }

        $file = $request->file('file');
        $fileName = $request->employee_id . '-' . $request->bulan . '-' . Str::random(6) . '.pdf';
        $filePath = $file->storeAs('salary-slips', $fileName, 'public');

        SalarySlip::create([
            'employee_id' => $request->employee_id,
            'bulan' => $request->bulan,
            'file_url' => '/storage/' . $filePath,
            'upload_by' => Auth::id() ?? 1,
        ]);

        return redirect()->back()->with('success', 'Salary slip berhasil ditambahkan');
    }

    public function preview(Request $request)
    {
        $employeeId = $request->integer('employee_id');
        $bulan = $request->string('bulan');

        $salarySlip = SalarySlip::where('employee_id', $employeeId)
            ->where('bulan', $bulan)
            ->first();

        if (!$salarySlip) {
            return response()->json(['error' => 'Salary slip not found'], 404);
        }

        $filePath = $salarySlip->file_url;

        // Remove /storage/ prefix if exists
        if (str_starts_with($filePath, '/storage/')) {
            $filePath = str_replace('/storage/', '', $filePath);
        }

        // Check if file exists in public storage
        if (Storage::disk('public')->exists($filePath)) {
            $fullPath = Storage::disk('public')->path($filePath);
            return response()->file($fullPath, [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'inline; filename="salary-slip.pdf"'
            ]);
        }

        // Check if file exists in public directory
        $publicPath = public_path($salarySlip->file_url);
        if (file_exists($publicPath)) {
            return response()->file($publicPath, [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'inline; filename="salary-slip.pdf"'
            ]);
        }

        return response()->json(['error' => 'File not found'], 404);
    }

    public function download(Request $request)
    {
        $employeeId = $request->integer('employee_id');
        $bulan = $request->string('bulan');

        $salarySlip = SalarySlip::where('employee_id', $employeeId)
            ->where('bulan', $bulan)
            ->first();

        if (!$salarySlip) {
            return response()->json(['error' => 'Salary slip not found'], 404);
        }

        $filePath = $salarySlip->file_url;

        // Remove /storage/ prefix if exists
        if (str_starts_with($filePath, '/storage/')) {
            $filePath = str_replace('/storage/', '', $filePath);
        }

        // Check if file exists in public storage
        if (Storage::disk('public')->exists($filePath)) {
            $fullPath = Storage::disk('public')->path($filePath);
            return response()->download($fullPath, 'salary-slip.pdf');
        }

        // Check if file exists in public directory
        $publicPath = public_path($salarySlip->file_url);
        if (file_exists($publicPath)) {
            return response()->download($publicPath, 'salary-slip.pdf');
        }

        return response()->json(['error' => 'File not found'], 404);
    }

    public function destroy(Request $request)
    {
        $employeeId = $request->integer('employee_id');
        $bulan = $request->string('bulan');

        $salarySlip = SalarySlip::where('employee_id', $employeeId)
            ->where('bulan', $bulan)
            ->first();

        if (!$salarySlip) {
            return back()->with('error', 'Slip gaji tidak ditemukan');
        }

        // Delete file from storage if exists
        $filePath = $salarySlip->file_url;
        if (str_starts_with($filePath, '/storage/')) {
            $filePath = str_replace('/storage/', '', $filePath);
            if (Storage::disk('public')->exists($filePath)) {
                Storage::disk('public')->delete($filePath);
            }
        }

        // Delete record from database
        $salarySlip->delete();

        return back()->with('success', 'Slip gaji berhasil dihapus');
    }
}
