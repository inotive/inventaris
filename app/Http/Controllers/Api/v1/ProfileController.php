<?php

namespace App\Http\Controllers\Api\v1;

use App\Helpers\ResponseFormatter;
use App\Http\Controllers\Controller;
use App\Http\Resources\DepartemenResource;
use App\Http\Resources\DepartmentResource;
use App\Http\Resources\UserResource;
use App\Models\Department;
use App\Models\EmployeeLeaveRequest;
use App\Models\Shift;
use App\Models\User;
use App\Enums\EmployeeLeaveRequestStatusEnum;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;

class ProfileController extends Controller
{
    public function index(Request $request)
    {
        $user = User::with([
            'employee.shift',
            'employee.leaveBalance',
            'employee.receivableBalance',
            'employee.department',
            'employee.attendances',
            'employee.documents',
            'employee.branch',
            'employee.salarySlips' => function ($query) {
                $query->orderByDesc('bulan')->limit(1);
            },
        ])->findOrFail($request->user()->id);
        $employeeId = $user->employee->id;
        $currentYear = now()->year;

        $totalQuota = \App\Models\EmployeeLeaveBalance::join('leave_types', 'employee_leave_balances.leave_type_id', '=', 'leave_types.id')
            ->where('employee_leave_balances.employee_id', $employeeId)
            ->where('leave_types.category', '!=', 'sick_leave')
            ->where('employee_leave_balances.year', $currentYear)
            ->sum('employee_leave_balances.total_quota');

        $cutiTerpakai = EmployeeLeaveRequest::where('employee_id', $employeeId)
            ->where('status', EmployeeLeaveRequestStatusEnum::APPROVED->value)
            ->whereYear('start_date', $currentYear)
            ->whereHas('leaveType', fn($q) => $q->where('category', 'annual_leave'))
            ->sum('total_days');

        $user->employee->leave_summary = [
            'cuti_terpakai' => (int) $cutiTerpakai,
            'sisa_cuti'     => (int) max(0, $totalQuota - $cutiTerpakai),
        ];


        return ResponseFormatter::success(new UserResource($user), 'Profil data berhasil diambil');
    }

    public function show($id)
    {
        $user = User::with(['employee.shift'])->findOrFail($id);
        return new UserResource($user);
    }

    public function update(Request $request)
    {
        $user = $request->user();
        if (!$user) {
            return ResponseFormatter::error('Unauthenticated', 401);
        }

        $validator = Validator::make($request->all(), [
            'name'              => 'nullable|string|max:255',
            'status'            => 'nullable|string|max:100',
            'age'               => 'nullable|integer|min:0|max:150',
            'email'             => 'nullable|email|unique:users,email,' . $user->id,
            'contact'           => 'nullable|string|max:30',
            'address'           => 'nullable|string|max:1000',
            'profile_photo_url' => 'nullable|image|mimes:jpeg,jpg,png|max:2048',
            'delete_photo'      => 'nullable|in:true,false,1,0',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors'  => $validator->errors(),
            ], 422);
        }

        DB::beginTransaction();
        $ageNotSaved = false;

        try {
            // Update user table
            if ($request->filled('email')) {
                $user->email = $request->input('email');
            }
            if ($request->filled('name')) {
                $user->name = $request->input('name');
            }

            // Handle profile photo deletion
            $deletePhoto = $request->input('delete_photo');
            if ($deletePhoto === true || $deletePhoto === 'true' || $deletePhoto === '1' || $deletePhoto === 1) {
                // Delete photo from storage if exists
                if ($user->profile_photo_path) {
                    \Storage::disk('public')->delete($user->profile_photo_path);
                }
                // Clear photo path from database
                $user->profile_photo_path = null;
            }
            // Handle profile photo upload
            elseif ($request->hasFile('profile_photo_url')) {
                // Delete old photo if exists
                if ($user->profile_photo_path) {
                    \Storage::disk('public')->delete($user->profile_photo_path);
                }

                $file = $request->file('profile_photo_url');
                $path = $file->store('profile-photos', 'public');
                $user->profile_photo_path = $path;
            }

            $user->save();

            // Update employee table
            $employee = $user->employee;

            if (!$employee) {
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'message' => 'Data karyawan tidak ditemukan',
                ], 404);
            }

            $employeeData = [];

            if ($request->filled('name')) {
                $employeeData['name'] = $request->input('name');
            }
            if ($request->filled('status')) {
                $employeeData['status'] = $request->input('status');
            }
            if ($request->filled('contact')) {
                $employeeData['contact'] = $request->input('contact');
            }
            if ($request->filled('address')) {
                $employeeData['address'] = $request->input('address');
            }
            if ($request->filled('age')) {
                if (Schema::hasColumn('employees', 'age')) {
                    $employeeData['age'] = $request->input('age');
                } else {
                    $ageNotSaved = true;
                }
            }

            if (!empty($employeeData)) {
                $employee->fill($employeeData)->save();
            }

            DB::commit();

            $userFresh = $user->fresh()->load([
                'employee.shift',
                'employee.leaveBalance',
                'employee.receivableBalance',
                'employee.department',
                'employee.branch',
            ]);

            // Set leave summary for response
            if ($userFresh->employee) {
                $empId = $userFresh->employee->id;
                $currentYear = now()->year;

                $totalQuota = \App\Models\EmployeeLeaveBalance::join('leave_types', 'employee_leave_balances.leave_type_id', '=', 'leave_types.id')
                    ->where('employee_leave_balances.employee_id', $empId)
                    ->where('leave_types.category', '=', 'annual_leave')
                    ->where('employee_leave_balances.year', $currentYear)
                    ->sum('employee_leave_balances.total_quota');

                $cutiTerpakai = EmployeeLeaveRequest::where('employee_id', $empId)
                    ->where('status', EmployeeLeaveRequestStatusEnum::APPROVED->value)
                    ->whereYear('start_date', $currentYear)
                    ->sum('total_days');

                $userFresh->employee->leave_summary = [
                    'cuti_terpakai' => (int) $cutiTerpakai,
                    'sisa_cuti'     => (int) max(0, $totalQuota - $cutiTerpakai),
                ];
            }

            $response = [
                'success' => true,
                'message' => 'Profil berhasil diperbarui',
                'data'    => new UserResource($userFresh),
            ];

            if ($ageNotSaved) {
                $response['warning'] = "Kolom 'age' tidak ditemukan di tabel employees; field 'age' tidak disimpan.";
            }

            return response()->json($response, 200);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Gagal memperbarui profil',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    public function getDepartmentsByBranch($branchId)
    {
        $departments = Department::where('branch_id', $branchId)->get();
        return ResponseFormatter::success(DepartemenResource::collection($departments), 'Departments fetched successfully');
    }

    public function getShifts()
    {
        $shifts = Shift::select('id', 'name', 'start_time', 'end_time')->get();
        return ResponseFormatter::success($shifts, 'Shifts fetched successfully');
    }
}
