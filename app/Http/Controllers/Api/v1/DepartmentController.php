<?php

namespace App\Http\Controllers\Api\v1;

use App\Helpers\ResponseFormatter;
use App\Http\Controllers\Controller;
use App\Http\Resources\DepartmentResource;
use App\Models\Department;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DepartmentController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();

        if (!$user || !$user->employee) {
            return ResponseFormatter::error('Employee not found', 404);
        }

        $departmentName = $user->employee->department->name ?? null;
        $search = $request->get('search');
        $limit = $request->get('limit', 10);


        $departments = Auth::user()->employee->department()->with(['checklists'])
            ->when($search, function ($query) use ($search) {
                $query->where('name', 'like', "%{$search}%")
                    ->orWhere('code', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            })->paginate($limit);

        return ResponseFormatter::successWithPagination(
            DepartmentResource::collection($departments),
            'departments',
            'List of Departments',
            $departments->total(),
            $departments->count(),
            $departments->perPage(),
            $departments->currentPage(),
            $departments->lastPage()
        );
    }

    public function show($id)
    {
        $department = Department::withCount('checklists')->find($id);

        if (!$department) {
            return ResponseFormatter::error('Department not found', 404);
        }

        return ResponseFormatter::success(
            new DepartmentResource($department),
            'Department detail',
            'department'
        );
    }

    public function getEmployees($id)
    {
        $department = Department::find($id);

        if (!$department) {
            return ResponseFormatter::error('Department not found', 404);
        }

        $employees = $department->employees()
            ->with(['user:id,name,email'])
            ->select('id', 'name', 'department_id', 'user_id')
            ->get()
            ->map(function ($employee) {
                return [
                    'id' => $employee->id,
                    'name' => $employee->name,
                    'email' => $employee->user?->email,
                ];
            });

        return ResponseFormatter::success(
            $employees,
            'Employees in department',
            'employees'
        );
    }
}
