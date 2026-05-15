<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        // Check permission
        abort_unless(Gate::allows('users.view'), 403, 'Anda tidak memiliki akses untuk melihat data pengguna');

        $sortBy = $request->get('sortBy', 'created_at');
        $sortDirection = $request->get('sortDirection', 'desc');
        $search = $request->input('search');
        $role = $request->input('role');
        $status = $request->input('status');
        $branchId = $request->input('branch_id');
        $positionId = $request->input('position_id');
        $perPage = $request->input('per_page', 10);

        // Handle "all" status
        if ($status == "all") {
            $status = null;
        }
        $users = User::with(['roles', 'employee.branch', 'employee.department', 'employee.shift'])
            ->when($search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('username', 'like', "%{$search}%");
                });
            })
            ->when($role, function ($query, $role) {
                $query->whereHas('roles', function ($q) use ($role) {
                    $q->where('name', $role);
                });
            })
            ->when($status, function ($query, $status) {
                $query->where('status', $status);
            })
            ->when($branchId, function ($query, $branchId) {
                $query->whereHas('employee', function ($q) use ($branchId) {
                    $q->where('branch_id', $branchId);
                });
            })
            ->when($positionId, function ($query, $positionId) {
                $query->whereHas('employee', function ($q) use ($positionId) {
                    $q->where('position_id', $positionId);
                });
            })
            ->orderBy($sortBy, $sortDirection)
            ->paginate($perPage)
            ->withQueryString();

        // Get counts for status tabs
        $statusCounts = [
            'all' => User::when($search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('username', 'like', "%{$search}%");
                });
            })
                ->when($role, function ($query, $role) {
                    $query->whereHas('roles', function ($q) use ($role) {
                        $q->where('name', $role);
                    });
                })
                ->when($branchId, function ($query, $branchId) {
                    $query->whereHas('employee', function ($q) use ($branchId) {
                        $q->where('branch_id', $branchId);
                    });
                })
                ->when($positionId, function ($query, $positionId) {
                    $query->whereHas('employee', function ($q) use ($positionId) {
                        $q->where('position_id', $positionId);
                    });
                })
                ->count(),
            'active' => User::when($search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('username', 'like', "%{$search}%");
                });
            })
                ->when($role, function ($query, $role) {
                    $query->whereHas('roles', function ($q) use ($role) {
                        $q->where('name', $role);
                    });
                })
                ->when($branchId, function ($query, $branchId) {
                    $query->whereHas('employee', function ($q) use ($branchId) {
                        $q->where('branch_id', $branchId);
                    });
                })
                ->when($positionId, function ($query, $positionId) {
                    $query->whereHas('employee', function ($q) use ($positionId) {
                        $q->where('position_id', $positionId);
                    });
                })
                ->where('status', 'active')
                ->count(),
            'pending' => User::when($search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('username', 'like', "%{$search}%");
                });
            })
                ->when($role, function ($query, $role) {
                    $query->whereHas('roles', function ($q) use ($role) {
                        $q->where('name', $role);
                    });
                })
                ->when($branchId, function ($query, $branchId) {
                    $query->whereHas('employee', function ($q) use ($branchId) {
                        $q->where('branch_id', $branchId);
                    });
                })
                ->when($positionId, function ($query, $positionId) {
                    $query->whereHas('employee', function ($q) use ($positionId) {
                        $q->where('position_id', $positionId);
                    });
                })
                ->where('status', 'pending')
                ->count(),
            'inactive' => User::when($search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('username', 'like', "%{$search}%");
                });
            })
                ->when($role, function ($query, $role) {
                    $query->whereHas('roles', function ($q) use ($role) {
                        $q->where('name', $role);
                    });
                })
                ->when($branchId, function ($query, $branchId) {
                    $query->whereHas('employee', function ($q) use ($branchId) {
                        $q->where('branch_id', $branchId);
                    });
                })
                ->when($positionId, function ($query, $positionId) {
                    $query->whereHas('employee', function ($q) use ($positionId) {
                        $q->where('position_id', $positionId);
                    });
                })
                ->where('status', 'inactive')
                ->count(),
        ];

        $roles = Role::all();
        $branches = \App\Models\Branch::all();
        $departments = \App\Models\Department::all();
        $shifts = \App\Models\Shift::all();
        $positions = \App\Models\Position::all();

        // Transform users to include branch_id, department_id, shift_id at user level
        $users->getCollection()->transform(function ($user) {
            $user->branch_id = $user->employee->branch_id ?? null;
            $user->department_id = $user->employee->department_id ?? null;
            $user->shift_id = $user->employee->shift_id ?? null;
            $user->working_start_date = $user->employee->working_start_date ?? null;
            $user->birthdate = $user->employee->birthdate ?? null;
            return $user;
        });
        
        $branchPositions = \App\Models\Employee::whereNotNull('branch_id')
            ->whereNotNull('position_id')
            ->select('branch_id', 'position_id')
            ->distinct()
            ->get()
            ->groupBy('branch_id')
            ->map(function ($items) {
                return $items->pluck('position_id');
            });

        return Inertia::render('Admin/Users/Index', [
            'users' => $users,
            'roles' => $roles,
            'branches' => $branches,
            'departments' => $departments,
            'shifts' => $shifts,
            'positions' => $positions,
            'branchPositions' => $branchPositions,
            'search' => $search,
            'role' => $role,
            'status' => $status,
            'branch_id' => $branchId,
            'position_id' => $positionId,
            'statusCounts' => $statusCounts,
            'sortBy' => $sortBy,
            'sortDirection' => $sortDirection,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        abort_unless(Gate::allows('users.create'), 403, 'Anda tidak memiliki akses untuk menambah pengguna');

        $roles = Role::all();

        return Inertia::render('Admin/Users/Create', [
            'roles' => $roles,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        abort_unless(Gate::allows('users.create'), 403, 'Anda tidak memiliki akses untuk menambah pengguna');

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'username' => ['required', 'string', 'max:255', 'unique:users,username'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'role' => ['required', 'string', 'exists:roles,name'],
            'status' => ['required', 'string', 'in:active,inactive,pending'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'username' => $validated['username'],
            'email' => $validated['email'],
            'status' => $validated['status'],
            'password' => Hash::make($validated['password']),
        ]);

        // Assign role
        $user->assignRole($validated['role']);

        return redirect()->back()->with('success', 'Pengguna berhasil ditambahkan');
    }

    /**
     * Display the specified resource.
     */
    public function show(User $user)
    {
        abort_unless(Gate::allows('users.view'), 403, 'Anda tidak memiliki akses untuk melihat detail pengguna');

        $user->load('roles', 'permissions');

        return Inertia::render('Admin/Users/Show', [
            'user' => $user,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(User $user)
    {
        abort_unless(Gate::allows('users.edit'), 403, 'Anda tidak memiliki akses untuk mengubah pengguna');

        $user->load('roles');
        $roles = Role::all();

        return Inertia::render('Admin/Users/Edit', [
            'user' => $user,
            'roles' => $roles,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, User $user)
    {
        abort_unless(Gate::allows('users.edit'), 403, 'Anda tidak memiliki akses untuk mengubah pengguna');

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'username' => ['required', 'string', 'max:255', Rule::unique('users')->ignore($user->id)],
            'email' => ['required', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'role' => ['required', 'string', 'exists:roles,name'],
            'status' => ['required', 'string', 'in:active,inactive,pending'],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
        ]);

        $updateData = [
            'name' => $validated['name'],
            'username' => $validated['username'],
            'email' => $validated['email'],
            'status' => $validated['status'],
        ];

        if (!empty($validated['password'])) {
            $updateData['password'] = Hash::make($validated['password']);
        }

        $user->update($updateData);

        // Sync role
        $user->syncRoles([$validated['role']]);

        return redirect()->back()->with('success', 'Pengguna berhasil diperbarui');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(User $user)
    {
        abort_unless(Gate::allows('users.delete'), 403, 'Anda tidak memiliki akses untuk menghapus pengguna');

        try {
            // Cek apakah user yang akan dihapus adalah user yang sedang login
            if ($user->id === Auth::id()) {
                return redirect()->back()->with('error', 'Tidak dapat menghapus akun sendiri');
            }

            // Cek apakah user memiliki relasi dengan employee
            if ($user->employee()->exists()) {
                return redirect()->back()->with('error', 'Data pengguna tidak dapat dihapus karena masih memiliki relasi dengan data karyawan');
            }

            $user->delete();

            return redirect()->back()->with('success', 'Pengguna berhasil dihapus');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Terjadi kesalahan saat menghapus pengguna: ' . $e->getMessage());
        }
    }

    /**
     * Verify user and create employee record
     */
    public function verify(Request $request, User $user)
    {
        abort_unless(Gate::allows('users.edit'), 403, 'Anda tidak memiliki akses untuk memverifikasi pengguna');

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'username' => ['required', 'string', 'max:255', Rule::unique('users')->ignore($user->id)],
            'email' => ['required', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'password' => ['nullable', 'string', 'min:8'],
            'branch_id' => ['required', 'integer', 'exists:branches,id'],
            'department_id' => ['required', 'integer', 'exists:departments,id'],
            'jabatan_id' => ['required', 'integer', 'exists:roles,id'],
            'position_id' => ['nullable', 'integer', 'exists:positions,id'],
            'shift_id' => ['required', 'integer', 'exists:shifts,id'],
            'working_start_date' => ['required', 'date'],
            'leave_quota_per_year' => ['required', 'integer', 'min:0'],
            'loan_quota' => ['nullable', 'numeric', 'min:0'],
            'salary' => ['nullable', 'numeric', 'min:0'],
            'contact' => ['nullable', 'string', 'max:20'],
            'gender' => ['required', 'in:Laki-laki,Perempuan'],
            'status' => ['required', 'in:Tetap,Kontrak,Magang'],
            'birthplace' => ['nullable', 'string', 'max:255'],
            'religion' => ['nullable', 'string'],
            'birthdate' => ['nullable', 'date'],
            'address' => ['nullable', 'string'],
            'provinsi' => ['nullable', 'string'],
            'kota' => ['nullable', 'string'],
            'kecamatan' => ['nullable', 'string'],
            'kelurahan' => ['nullable', 'string'],
            'nik' => ['nullable', 'string', 'max:16'],
            'ktp' => ['nullable', 'string', 'max:20'],
            'bpjs_kesehatan' => ['nullable', 'string'],
            'bpjs_ketenagakerjaan' => ['nullable', 'string'],
            'certificate' => ['nullable', 'string'],
            'contract' => ['nullable', 'string'],
            'photo' => ['nullable', 'image', 'max:2048'],
            'signature' => ['nullable', 'image', 'max:2048'],
        ]);

        try {
            \DB::beginTransaction();

            \Log::info('Verifying user', [
                'user_id' => $user->id,
                'username' => $validated['username'],
                'name' => $validated['name'],
                'jabatan_id' => $validated['jabatan_id'] ?? null,
            ]);

            // Handle photo upload
            $photoPath = null;
            $fileName = null;
            if ($request->hasFile('photo')) {
                $file = $request->file('photo');
                $fileName = 'employee_' . time() . '.' . $file->getClientOriginalExtension();
                $photoPath = $file->storeAs('uploads/employees', $fileName, 'public');

                // Update user profile photo
                $user->profile_photo_path = $photoPath;
            }

            // Update user data
            $userData = [
                'name' => $validated['name'],
                'username' => $validated['username'],
                'email' => $validated['email'],
                'status' => 'active', // Activate user
            ];

            if (!empty($validated['password'])) {
                $userData['password'] = Hash::make($validated['password']);
            }

            $user->update($userData);

            // Assign role
            $role = \Spatie\Permission\Models\Role::find($validated['jabatan_id']);
            if ($role) {
                $user->syncRoles([$role->name]);
            }

            // Create or update employee record
            $employeeData = [
                'user_id' => $user->id,
                'name' => $validated['name'],
                'branch_id' => $validated['branch_id'],
                'department_id' => $validated['department_id'],
                'position_id' => $validated['position_id'] ?? null,
                'shift_id' => $validated['shift_id'],
                'working_start_date' => $validated['working_start_date'],
                'salary' => $validated['salary'] ?? null,
                'contact' => $validated['contact'] ?? null,
                'address' => $validated['address'] ?? null,
                'provinsi' => $validated['provinsi'] ?? null,
                'kota' => $validated['kota'] ?? null,
                'kecamatan' => $validated['kecamatan'] ?? null,
                'kelurahan' => $validated['kelurahan'] ?? null,
                'gender' => $validated['gender'],
                'status' => $validated['status'],
                'birthplace' => $validated['birthplace'] ?? null,
                'religion' => $validated['religion'] ?? null,
                'nik' => $validated['nik'] ?? null,
                'ktp' => $validated['ktp'] ?? null,
                'bpjs_kesehatan' => $validated['bpjs_kesehatan'] ?? null,
                'bpjs_ketenagakerjaan' => $validated['bpjs_ketenagakerjaan'] ?? null,
                'certificate' => $validated['certificate'] ?? null,
                'contract' => $validated['contract'] ?? null,
                'verification' => true,
            ];

            // Optional quotas per employee
            if (\Illuminate\Support\Facades\Schema::hasColumn('employees', 'leave_quota_per_year')) {
                $employeeData['leave_quota_per_year'] = (int) ($validated['leave_quota_per_year'] ?? 0);
            }
            if (\Illuminate\Support\Facades\Schema::hasColumn('employees', 'loan_quota')) {
                $employeeData['loan_quota'] = (int) ($validated['loan_quota'] ?? 0);
            }

            // Optional birthdate
            if (\Illuminate\Support\Facades\Schema::hasColumn('employees', 'birthdate') && !empty($validated['birthdate'])) {
                $employeeData['birthdate'] = $validated['birthdate'];
            }

            if ($photoPath) {
                $employeeData['file_name'] = $fileName;
                $employeeData['path'] = $photoPath;
            }

            if ($user->employee) {
                $user->employee->update($employeeData);
                $employee = $user->employee;
            } else {
                $employee = \App\Models\Employee::create($employeeData);
            }

            // Save signature document if uploaded
            if ($request->hasFile('signature')) {
                $sigFile = $request->file('signature');
                $sigPath = $sigFile->storeAs('signatures', time() . '_' . $sigFile->getClientOriginalName(), 'public');
                \App\Models\EmployeeDocument::updateOrCreate(
                    [
                        'employee_id' => $employee->id,
                        'title' => 'Tanda Tangan',
                    ],
                    [
                        'file_path' => $sigPath,
                    ]
                );
            }

            // Save optional identity documents
            $documentMappings = [
                ['title' => 'KTP', 'file_field' => 'ktp_file'],
                ['title' => 'BPJS Kesehatan', 'file_field' => 'bpjs_kesehatan_file'],
                ['title' => 'BPJS Ketenagakerjaan', 'file_field' => 'bpjs_ketenagakerjaan_file'],
                ['title' => 'Sertifikat', 'file_field' => 'certificate_file'],
                ['title' => 'Kontrak', 'file_field' => 'contract_file'],
                ['title' => 'Kartu Keluarga', 'file_field' => 'kk_file'],
            ];

            foreach ($documentMappings as $doc) {
                if ($request->hasFile($doc['file_field'])) {
                    $docFile = $request->file($doc['file_field']);
                    $docPath = $docFile->storeAs('employee_documents', time() . '_' . $docFile->getClientOriginalName(), 'public');
                    \App\Models\EmployeeDocument::updateOrCreate(
                        [
                            'employee_id' => $employee->id,
                            'title' => $doc['title'],
                        ],
                        [
                            'file_path' => $docPath,
                        ]
                    );
                }
            }

            \Log::info('Employee documents updated for employee ' . $employee->name . ' (id=' . $employee->id . ')');

            // Auto create shift schedule for 1 month ahead starting from working_start_date
            try {
                $start = \Carbon\Carbon::parse($employee->working_start_date)->startOfDay();
                $end = $start->copy()->addMonthNoOverflow()->subDay();
                $shiftModel = \App\Models\Shift::find($employee->shift_id);
                $pattern = (array) optional($shiftModel)->weekly_pattern ?: [];

                $rows = [];
                $cursor = $start->copy();
                $nowTs = now();
                $dowKeys = ['sun', 'mon', 'tue', 'wed', 'thu', 'fri', 'sat'];
                while ($cursor->lte($end)) {
                    $dow = $dowKeys[$cursor->dayOfWeek];
                    $include = empty($pattern) ? true : (bool) ($pattern[$dow] ?? false);
                    if ($include) {
                        $rows[] = [
                            'employee_id' => $employee->id,
                            'department_id' => $employee->department_id,
                            'shift_id' => $employee->shift_id,
                            'work_date' => $cursor->toDateString(),
                            'created_at' => $nowTs,
                            'updated_at' => $nowTs,
                        ];
                    }
                    $cursor->addDay();
                }
                if (!empty($rows)) {
                    \App\Models\AttendanceShiftWork::upsert($rows, ['employee_id', 'work_date'], ['shift_id', 'department_id', 'updated_at']);
                }
                \Log::info('Shift schedule created for employee ' . $employee->name);
            } catch (\Throwable $e) {
                \Log::error('Failed to create shift schedule: ' . $e->getMessage());
            }

            // Auto create leave balances based on employee/role and leave types
            try {
                \Log::info('Leave balance creation started', [
                    'employee_id' => $employee->id,
                    'jabatan_id' => $validated['jabatan_id'] ?? null,
                ]);

                $year = now()->year;

                if (\Illuminate\Support\Facades\Schema::hasTable('leave_types')) {
                    // Annual leave balance
                    \App\Models\EmployeeLeaveBalance::firstOrCreate(
                        [
                            'employee_id' => $employee->id,
                            'leave_type_id' => 1,
                            'year' => $year,
                        ],
                        [
                            'total_quota' => (int) $validated['leave_quota_per_year'],
                            'used_quota' => 0,
                            'remaining_quota' => (int) $validated['leave_quota_per_year'],
                        ]
                    );

                    // Special leave types
                    $leaveTypes = \App\Models\LeaveType::where('category', \App\Enums\LeaveTypeCategoryEnum::SPECIAL_LEAVE)->get();
                    foreach ($leaveTypes as $type) {
                        \App\Models\EmployeeLeaveBalance::firstOrCreate(
                            [
                                'employee_id' => $employee->id,
                                'leave_type_id' => $type->id,
                                'year' => $year,
                            ],
                            [
                                'total_quota' => $type->leave_quota_per_year,
                                'used_quota' => 0,
                                'remaining_quota' => $type->leave_quota_per_year,
                            ]
                        );
                    }
                    \Log::info('Leave balances created for employee ' . $employee->name);
                }
            } catch (\Throwable $e) {
                \Log::error('Failed to create leave balances: ' . $e->getMessage());
            }

            // Auto create receivable balance based on employee/role loan quota
            try {
                \Log::info('Receivable balance creation started', [
                    'employee_id' => $employee->id,
                    'jabatan_id' => $validated['jabatan_id'] ?? null,
                ]);

                $roleModel = \Spatie\Permission\Models\Role::find($validated['jabatan_id'] ?? null);
                $empLoan = (int) ($validated['loan_quota'] ?? 0);
                $roleLoan = (int) (optional($roleModel)->loan_quota ?? 0);
                $limit = $empLoan > 0 ? $empLoan : $roleLoan;

                \App\Models\EmployeeReceivableBalance::firstOrCreate(
                    [
                        'employee_id' => $employee->id,
                        'period_year' => now()->year,
                        'period_month' => now()->month,
                    ],
                    [
                        'limit_amount' => $limit,
                        'used_amount' => 0,
                        'remaining_amount' => $limit,
                        'policy' => 'yearly',
                    ]
                );
                \Log::info('Receivable balance created for employee ' . $employee->name);
            } catch (\Throwable $e) {
                \Log::error('Failed to create receivable balance: ' . $e->getMessage());
            }

            \DB::commit();

            return redirect()->back()->with('success', 'Pengguna berhasil diverifikasi dan data karyawan telah dibuat');
        } catch (\Exception $e) {
            \DB::rollBack();
            \Log::error('Error verifying user: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Terjadi kesalahan saat memverifikasi pengguna: ' . $e->getMessage());
        }
    }
}
