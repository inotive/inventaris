<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\EmployeeLeaveBalance;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class EmployeeLeaveBalanceController extends Controller
{
    /**
     * Store or update leave balance for an employee
     */
    public function store(Request $request, Employee $employee)
    {
        // Validate request
        $validated = $request->validate([
            'leave_type_id' => 'required|exists:leave_types,id',
            'year' => 'required|integer|min:2000|max:2100',
            'total_quota' => 'required|integer|min:0',
        ], [
            'leave_type_id.required' => 'Jenis cuti wajib dipilih',
            'leave_type_id.exists' => 'Jenis cuti tidak valid',
            'year.required' => 'Tahun wajib diisi',
            'year.integer' => 'Tahun harus berupa angka',
            'year.min' => 'Tahun minimal 2000',
            'year.max' => 'Tahun maksimal 2100',
            'total_quota.required' => 'Jumlah hari wajib diisi',
            'total_quota.integer' => 'Jumlah hari harus berupa angka',
            'total_quota.min' => 'Jumlah hari tidak boleh negatif',
        ]);

        try {
            // Check if leave balance already exists
            $existing = EmployeeLeaveBalance::where([
                'employee_id' => $employee->id,
                'leave_type_id' => $validated['leave_type_id'],
                'year' => $validated['year'],
            ])->first();

            if ($existing) {
                // Update existing leave balance
                $existing->update([
                    'total_quota' => $validated['total_quota'],
                    'remaining_quota' => $validated['total_quota'] - $existing->used_quota,
                ]);

                // If this is annual leave (leave_type_id = 1), also update employee's leave_quota_per_year
                if ($validated['leave_type_id'] == 1 && \Illuminate\Support\Facades\Schema::hasColumn('employees', 'leave_quota_per_year')) {
                    $employee->update([
                        'leave_quota_per_year' => $validated['total_quota'],
                    ]);

                    Log::info('Employee leave_quota_per_year updated', [
                        'employee_id' => $employee->id,
                        'leave_quota_per_year' => $validated['total_quota'],
                    ]);
                }

                Log::info('Leave balance updated', [
                    'employee_id' => $employee->id,
                    'leave_type_id' => $validated['leave_type_id'],
                    'year' => $validated['year'],
                    'total_quota' => $validated['total_quota'],
                ]);

                return redirect()->back()->with('success', 'Hak cuti berhasil diperbarui');
            } else {
                // Create new leave balance
                EmployeeLeaveBalance::create([
                    'employee_id' => $employee->id,
                    'leave_type_id' => $validated['leave_type_id'],
                    'year' => $validated['year'],
                    'total_quota' => $validated['total_quota'],
                    'used_quota' => 0,
                    'remaining_quota' => $validated['total_quota'],
                ]);

                // If this is annual leave (leave_type_id = 1), also update employee's leave_quota_per_year
                if ($validated['leave_type_id'] == 1 && \Illuminate\Support\Facades\Schema::hasColumn('employees', 'leave_quota_per_year')) {
                    $employee->update([
                        'leave_quota_per_year' => $validated['total_quota'],
                    ]);

                    Log::info('Employee leave_quota_per_year updated', [
                        'employee_id' => $employee->id,
                        'leave_quota_per_year' => $validated['total_quota'],
                    ]);
                }

                Log::info('Leave balance created', [
                    'employee_id' => $employee->id,
                    'leave_type_id' => $validated['leave_type_id'],
                    'year' => $validated['year'],
                    'total_quota' => $validated['total_quota'],
                ]);

                return redirect()->back()->with('success', 'Hak cuti berhasil ditambahkan');
            }
        } catch (\Exception $e) {
            Log::error('Failed to store leave balance', [
                'employee_id' => $employee->id,
                'error' => $e->getMessage(),
            ]);

            return redirect()->back()->withErrors(['error' => 'Gagal menyimpan hak cuti: ' . $e->getMessage()]);
        }
    }
}
