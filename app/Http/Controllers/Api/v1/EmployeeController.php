<?php

namespace App\Http\Controllers\Api\v1;

use App\Helpers\ResponseFormatter;
use App\Http\Controllers\Controller;
use App\Http\Resources\EmployeeResource;
use App\Models\Employee;
use Illuminate\Http\Request;

class EmployeeController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->get('search');
        $branchId = $request->get('branch_id');
        $departmentId = $request->get('department_id');
        $limit = $request->get('limit', 10);

        $employees = Employee::with(['user', 'branch', 'department'])
            ->where('status', '!=', 'Tidak Aktif')
            ->whereNull('resign_date')
            ->when($search, function ($query) use ($search) {
                $query->where('name', 'like', "%{$search}%")
                    ->orWhere('contact', 'like', "%{$search}%")
                    ->orWhere('nik', 'like', "%{$search}%")
                    ->orWhere('ktp', 'like', "%{$search}%");
            })
            ->when($branchId, function ($query) use ($branchId) {
                $query->where('branch_id', $branchId);
            })
            ->when($departmentId, function ($query) use ($departmentId) {
                $query->where('department_id', $departmentId);
            })
            ->where(function ($query) {
                $user = auth()->user();
                $isSuperadmin = $user->hasRole('Superadmin');
                $isBranch2 = ($user->employee && $user->employee->branch_id == 2);

                if (!$isSuperadmin && !$isBranch2 && $user->employee) {
                    $query->where('branch_id', $user->employee->branch_id);
                }
            })
            ->paginate($limit);

        return ResponseFormatter::successWithPagination(
            EmployeeResource::collection($employees->items()),
            'employees',
            'List of Employees',
            $employees->total(),
            $employees->count(),
            $employees->perPage(),
            $employees->currentPage(),
            $employees->lastPage()
        );
    }

    public function show($id)
    {
        $employee = Employee::with(['user', 'branch', 'department'])
            ->where('status', '!=', 'Tidak Aktif')
            ->whereNull('resign_date')
            ->find($id);

        if (!$employee) {
            return ResponseFormatter::error('Employee not found', 404);
        }

        return ResponseFormatter::success(
            new EmployeeResource($employee),
            'Employee detail retrieved successfully'
        );
    }
}
