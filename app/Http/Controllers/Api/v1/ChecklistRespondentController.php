<?php

namespace App\Http\Controllers\Api\v1;

use App\Helpers\ResponseFormatter;
use App\Http\Controllers\Controller;
use App\Models\Checklist;
use App\Models\Employee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class ChecklistRespondentController extends Controller
{
    /**
     * Get employees filtered by branch_id or department_id
     */
    public function getEmployees(Request $request, Checklist $checklist)
    {
        try {
            if (!Auth::guard('web')->check()) {
                return ResponseFormatter::error('Unauthenticated', 401);
            }

            $user = Auth::guard('web')->user();
            $isAdmin = $user->hasRole('Superadmin', 'web') || $user->hasRole('Admin', 'web');

            if (!$isAdmin) {
                return ResponseFormatter::error('Unauthorized to view employees', 403);
            }

            $branchId = $request->input('branch_id');
            $departmentId = $request->input('department_id');

            $query = Employee::with(['department:id,name', 'branch:id,name']);

            if ($departmentId) {
                $query->where('department_id', $departmentId);
            } elseif ($branchId) {
                $query->where('branch_id', $branchId);
            }

            $employees = $query->orderBy('name')->get();

            $formattedEmployees = $employees->map(function ($emp) {
                return [
                    'id' => $emp->id,
                    'name' => $emp->name,
                    'department' => $emp->department ? ['id' => $emp->department->id, 'name' => $emp->department->name] : null,
                    'branch' => $emp->branch ? ['id' => $emp->branch->id, 'name' => $emp->branch->name] : null,
                ];
            });

            return ResponseFormatter::success($formattedEmployees, 'Employees retrieved successfully');
        } catch (\Exception $e) {
            return ResponseFormatter::error('Failed to retrieve employees: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Get all respondents for a checklist
     */
    public function index(Request $request, Checklist $checklist)
    {
        try {
            if (!Auth::guard('web')->check()) {
                return ResponseFormatter::error('Unauthenticated', 401);
            }

            $user = Auth::guard('web')->user();
            $isAdmin = $user->hasRole('Superadmin', 'web') || $user->hasRole('Admin', 'web');

            if (!$isAdmin) {
                return ResponseFormatter::error('Unauthorized to view respondents', 403);
            }

            $perPage = (int) $request->input('per_page', 10);
            $cursor = $request->input('cursor');
            $search = $request->input('search', '');

            $query = $checklist->employees()->with(['department:id,name', 'branch:id,name']);

            // Apply search filter
            if ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('employees.name', 'like', "%{$search}%")
                        ->orWhereHas('department', function ($deptQuery) use ($search) {
                            $deptQuery->where('name', 'like', "%{$search}%");
                        })
                        ->orWhereHas('branch', function ($branchQuery) use ($search) {
                            $branchQuery->where('name', 'like', "%{$search}%");
                        });
                });
            }

            // Apply cursor pagination
            if ($cursor) {
                $respondents = $query->cursorPaginate($perPage, ['*'], 'cursor', $cursor);
            } else {
                $respondents = $query->cursorPaginate($perPage);
            }

            return ResponseFormatter::success($respondents, 'Respondents retrieved successfully');
        } catch (\Exception $e) {
            return ResponseFormatter::error('Failed to retrieve respondents: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Add respondents to a checklist
     */
    public function store(Request $request, Checklist $checklist)
    {
        try {
            if (!Auth::guard('web')->check()) {
                return ResponseFormatter::error('Unauthenticated', 401);
            }

            $user = Auth::guard('web')->user();
            $isAdmin = $user->hasRole('Superadmin', 'web') || $user->hasRole('Admin', 'web');

            if (!$isAdmin) {
                return ResponseFormatter::error('Unauthorized to add respondents', 403);
            }

            $validator = Validator::make($request->all(), [
                'employee_ids' => 'required|array|min:1',
                'employee_ids.*' => 'required|integer|exists:employees,id',
            ], [
                'employee_ids.required' => 'Employee IDs wajib diisi',
                'employee_ids.array' => 'Employee IDs harus berupa array',
                'employee_ids.min' => 'Minimal harus ada satu employee',
                'employee_ids.*.required' => 'Setiap employee ID wajib diisi',
                'employee_ids.*.integer' => 'Employee ID harus berupa angka',
                'employee_ids.*.exists' => 'Employee tidak ditemukan',
            ]);

            if ($validator->fails()) {
                return ResponseFormatter::error('Validation failed', 422, $validator->errors());
            }

            DB::beginTransaction();

            try {
                // Attach employees to checklist (sync will add new ones without removing existing)
                $existingIds = $checklist->employees()->pluck('employees.id')->toArray();
                $newIds = array_diff($request->employee_ids, $existingIds);

                if (!empty($newIds)) {
                    $checklist->employees()->attach($newIds);
                }

                DB::commit();

                // Get updated respondents
                $respondents = $checklist->employees()->with(['department:id,name', 'branch:id,name'])->get();
                $formattedRespondents = $respondents->map(function ($emp) {
                    return [
                        'id' => $emp->id,
                        'name' => $emp->name,
                        'department' => $emp->department ? ['id' => $emp->department->id, 'name' => $emp->department->name] : null,
                        'branch' => $emp->branch ? ['id' => $emp->branch->id, 'name' => $emp->branch->name] : null,
                    ];
                });

                return ResponseFormatter::success($formattedRespondents, 'Respondents added successfully');
            } catch (\Exception $e) {
                DB::rollBack();
                throw $e;
            }
        } catch (\Exception $e) {
            return ResponseFormatter::error('Failed to add respondents: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Remove a respondent from a checklist
     */
    public function destroy(Checklist $checklist, $employeeId)
    {
        try {
            if (!Auth::guard('web')->check()) {
                return ResponseFormatter::error('Unauthenticated', 401);
            }

            $user = Auth::guard('web')->user();
            $isAdmin = $user->hasRole('Superadmin', 'web') || $user->hasRole('Admin', 'web');

            if (!$isAdmin) {
                return ResponseFormatter::error('Unauthorized to remove respondents', 403);
            }

            $employee = Employee::find($employeeId);
            if (!$employee) {
                return ResponseFormatter::error('Employee not found', 404);
            }

            DB::beginTransaction();

            try {
                $checklist->employees()->detach($employeeId);

                DB::commit();

                return ResponseFormatter::success(null, 'Respondent removed successfully');
            } catch (\Exception $e) {
                DB::rollBack();
                throw $e;
            }
        } catch (\Exception $e) {
            return ResponseFormatter::error('Failed to remove respondent: ' . $e->getMessage(), 500);
        }
    }
}

