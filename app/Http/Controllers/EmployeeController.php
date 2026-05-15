<?php

namespace App\Http\Controllers;

use App\Enums\LeaveTypeCategoryEnum;
use App\Http\Requests\Employee\StoreEmployeeRequest;
use App\Models\Branch;
use App\Models\Department;
use App\Models\User;
use App\Models\Shift;
use App\Models\Attendance;
use App\Models\EmployeeOvertime;
use App\Models\EmployeeLeaveRequest;
use Spatie\Permission\Models\Role as SpatieRole;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;
use App\Models\Employee;
use App\Models\EmployeeLeaveBalance;
use App\Models\LeaveType;
use App\Models\Position;
use App\Models\Role;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Spatie\Permission\Models\Permission;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use App\Traits\HandlesEmployeeFileUpload;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;

class EmployeeController extends Controller
{
    use HandlesEmployeeFileUpload;
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $sortBy = $request->get('sort_by', 'name');
        $sortDirection = $request->get('sort_direction', 'asc');

        $search = $request->input('search');
        $perPage = $request->input('per_page', 10);

        $user = Auth::user();
        $isSuperadmin = $user->hasRole("Superadmin");

        $userBranchId = $user->employee->branch_id ?? null;

        // If user status is "active" and no active_status filter from request, default to "active"
        $activeStatus = $request->input('active_status') ?: ($user->status === 'active' ? 'active' : null);

        $branchOptions = Branch::select([
            'id',
            'name',
        ])->when(!$isSuperadmin && $userBranchId != 2, function ($query) {
            $query->where('id', Auth::user()->employee->branch_id);
        })->get();

        $departmentOptions = Department::select([
            'id',
            'name',
            'branch_id',
        ])->when(!$isSuperadmin && $userBranchId != 2, function ($query) {
            $query->where('branch_id', Auth::user()->employee->branch_id);
        })->get();


        $employees = Employee::with(['user.roles', 'branch', 'department', 'shift', 'role', 'position'])
            ->when($search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhereHas('user', function ($uq) use ($search) {
                            $uq->where('username', 'like', "%{$search}%");
                        });
                });
            })
            ->when(!$isSuperadmin && $userBranchId != 2, function ($query) {
                $query->where('branch_id', Auth::user()->employee->branch_id);
            })
            ->when($request->filled('name'), function ($query) use ($request) {
                $term = $request->string('name')->toString();
                $query->where(function ($q) use ($term) {
                    $q->where('name', 'like', "%{$term}%")
                        ->orWhereHas('user', function ($uq) use ($term) {
                            $uq->where('username', 'like', "%{$term}%");
                        });
                });
            })
            ->when($request->filled('branch_id'), function ($query) use ($request) {
                $query->where('branch_id', (int) $request->branch_id);
            })
            ->when($request->filled('department_id'), function ($query) use ($request) {
                $query->where('department_id', (int) $request->department_id);
            })
            ->when($activeStatus, function ($query) use ($activeStatus) {
                $query->whereHas('user', function ($q) use ($activeStatus) {
                    $q->where('status', $activeStatus);
                });
            })
            // ->when($request->permission_id, function ($query, $permissionId) {
            //     $query->where('permission_id', $permissionId);
            // })
            ->when($request->filled('gender'), function ($query) use ($request) {
                $gender = $request->string('gender')->toString();
                $query->where('gender', 'like', "%{$gender}%");
            })
            ->when($request->filled('status'), function ($query) use ($request) {
                if ($request->string('status')->toString() == 'belum_verifikasi') {
                    $query->where('verification', false);
                } else {
                    $query->where('verification', true)->where('status', $request->string('status')->toString());
                }
            })
            ->when($request->filled('shift_id'), function ($query) use ($request) {
                $query->where('shift_id', (int) $request->shift_id);
            })
            ->when($request->filled('phone'), function ($query) use ($request) {
                $phone = $request->string('phone')->toString();
                $query->where('contact', 'like', "%{$phone}%");
            })
            ->when($request->filled('role_id'), function ($query) use ($request) {
                $roleId = (int) $request->role_id;
                $query->whereHas('user.roles', function ($rq) use ($roleId) {
                    $rq->where('id', $roleId);
                });
            })
            ->when($request->filled('position_id'), function ($query) use ($request) {
                $query->where('position_id', (int) $request->position_id);
            })
            ->when($request->filled('religion'), function ($query) use ($request) {
                $religion = $request->string('religion')->toString();
                $query->where('religion', 'like', "%{$religion}%");
            })
            ->when($request->filled('contact'), function ($query) use ($request) {
                $contact = $request->string('contact')->toString();
                $query->where('contact', 'like', "%{$contact}%");
            })
            ->when($request->filled('birthplace'), function ($query) use ($request) {
                $birthplace = $request->string('birthplace')->toString();
                $query->where('birthplace', 'like', "%{$birthplace}%");
            })
            ->orderBy($sortBy, $sortDirection)
            ->paginate($perPage)
            ->withQueryString();



        return Inertia::render('Admin/Employees/Index', [
            'employees' => $employees,
            'sort_by' => $sortBy,
            'sort_direction' => $sortDirection,
            'search' => $search,
            'branches' => $branchOptions,
            'departments' => $departmentOptions,
            // 'permissions' => Permission::select('id', 'name')->get(),
            'roles' => SpatieRole::select('id', 'name')->get(),
            'positions' => Position::select('id', 'name')->get(),
            'shifts' => Shift::select('id', 'name')->get(),
            'status_options' => Employee::select('status')->whereNotNull('status')->distinct()->pluck('status'),
            'groupBy' => $request->string('groupBy')->toString(),
            'filters' => [
                'name' => $request->string('name')->toString(),
                'gender' => $request->string('gender')->toString(),
                'department_id' => $request->input('department_id'),
                'branch_id' => $request->input('branch_id'),
                'role_id' => $request->input('role_id'),
                'shift_id' => $request->input('shift_id'),
                'status' => $request->string('status')->toString(),
                'active_status' => $activeStatus ?? '',
                'permission_id' => $request->input('permission_id'),
            ],
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        abort_unless(Gate::allows('employees.create'), 403, 'Anda tidak memiliki akses untuk membuat karyawan');

        $branches = Branch::all();
        $departments = Department::all();
        // $permissions = Permission::all();
        $shift = Shift::all();
        $role = SpatieRole::all();
        $positions = Position::all();
        return Inertia::render('Admin/Employees/Create', [
            'branches' => $branches,
            'departments' => $departments,
            'positions' => $positions,
            //  'permissions' => $permissions,
            'shifts' => $shift,
            'roles' => $role,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreEmployeeRequest $request)
    {
        $data = $request->validated();
        try {
            Log::info('Creating new employee', [
                'username' => $data['username'],
                'name' => $data['name'],
                'email' => $data['email'],
                'jabatan_id' => $data['jabatan_id'] ?? null,
                'position_id' => $data['position_id'] ?? null,
            ]);

            DB::beginTransaction();

            $data['jabatan_id'] = $data['jabatan_id'] ?? 3;

            // Upload employee photo using trait FIRST
            $photoData = $this->uploadEmployeePhoto($request);
            $fileName = $photoData['file_name'];
            $storedPath = $photoData['path'];

            $user = new User();
            $user->name = $data['name'];
            $user->username = $data['username'];
            $user->email = $data['email'];
            $user->password = Hash::make($data['password']);
            // Set profile_photo_path for user profile display
            if ($storedPath) {
                $user->profile_photo_path = $storedPath;
            }
            $user->save();
            // assign role to user according to selected jabatan
            if (!empty($data['jabatan_id'])) {
                $roleModel = \Spatie\Permission\Models\Role::find($data['jabatan_id']);
                if ($roleModel) {
                    $user->assignRole($roleModel->name);
                }
            }


            $employee              = new Employee();
            $employee->user_id     = $user->id;
            $employee->name        = $data['name'];
            $employee->branch_id   = $data['branch_id'];
            $employee->department_id = $data['department_id'];
            // $employee->permission_id = $data['permission_id'];
            $employee->position_id = $data['position_id']; // from positions table
            $employee->shift_id    = $data['shift_id'];
            $employee->working_start_date = $data['working_start_date'];
            $employee->salary      = $data['salary'];
            // optional quotas per employee
            if (\Illuminate\Support\Facades\Schema::hasColumn('employees', 'leave_quota_per_year')) {
                $employee->leave_quota_per_year = (int) ($data['leave_quota_per_year'] ?? 0);
            }
            if (\Illuminate\Support\Facades\Schema::hasColumn('employees', 'loan_quota')) {
                $employee->loan_quota = (int) ($data['loan_quota'] ?? 0);
            }
            // optional fields
            $employee->contact = $data['contact'] ?? null;
            $employee->address = $data['address'] ?? null;
            $employee->provinsi = $data['provinsi'] ?? null;
            $employee->kota = $data['kota'] ?? null;
            $employee->kecamatan = $data['kecamatan'] ?? null;
            $employee->kelurahan = $data['kelurahan'] ?? null;
            $employee->gender = $data['gender'] ?? null;
            $employee->status = $data['status'] ?? null;
            $employee->birthplace = $data['birthplace'] ?? null;
            $employee->religion = $data['religion'] ?? null;
            $employee->nik = $data['nik'] ?? null;
            $employee->ktp = $data['ktp'] ?? null;
            $employee->bpjs_kesehatan = $data['bpjs_kesehatan'] ?? null;
            $employee->bpjs_ketenagakerjaan = $data['bpjs_ketenagakerjaan'] ?? null;
            $employee->certificate = $data['certificate'] ?? null;
            $employee->contract = $data['contract'] ?? null;
            // Optional: resign_date only if column exists
            try {
                if (\Illuminate\Support\Facades\Schema::hasColumn('employees', 'resign_date')) {
                    $employee->resign_date = $request->input('resign_date') ? Carbon::parse($request->input('resign_date'))->format('Y-m-d') : null;
                }
            } catch (\Throwable $e) {
            }
            // mark as verified by default
            if (\Illuminate\Support\Facades\Schema::hasColumn('employees', 'verification')) {
                $employee->verification = true;
            }
            // Optional: birthdate only if column exists
            try {
                if (\Illuminate\Support\Facades\Schema::hasColumn('employees', 'birthdate')) {
                    $employee->birthdate = $request->input('birthdate');
                }
            } catch (\Throwable $e) {
            }

            if ($fileName) {
                $employee->file_name = $fileName;
                $employee->path      = $storedPath;
            }

            // Role is already assigned when creating user, so no need to sync here

            $employee->save();

            // Save optional identity documents using trait batch method
            $this->saveEmployeeDocumentsBatch($request, $employee->id, [
                [
                    'title' => 'KTP',
                    'file_field' => 'ktp_file',
                    'url_field' => 'ktp_url',
                ],
                [
                    'title' => 'BPJS Kesehatan',
                    'file_field' => 'bpjs_kesehatan_file',
                    'url_field' => 'bpjs_kesehatan_url',
                ],
                [
                    'title' => 'BPJS Ketenagakerjaan',
                    'file_field' => 'bpjs_ketenagakerjaan_file',
                    'url_field' => 'bpjs_ketenagakerjaan_url',
                ],
                [
                    'title' => 'Sertifikat',
                    'file_field' => 'certificate_file',
                    'url_field' => 'certificate_url',
                ],
                [
                    'title' => 'Kontrak',
                    'file_field' => 'contract_file',
                    'url_field' => 'contract_url',
                ],
                [
                    'title' => 'Kartu Keluarga',
                    'file_field' => 'kk_file',
                    'url_field' => 'kk_url',
                ],
            ]);

            // Save signature document if uploaded using trait
            $this->uploadEmployeeSignature($request, $employee->id);

            Log::info('Employee documents updated for employee ' . $employee->name . ' (id=' . $employee->id . ')');


            // Auto create shift schedule for 1 month ahead starting from working_start_date
            try {
                $start = \Carbon\Carbon::parse($employee->working_start_date)->startOfDay();
                $end = $start->copy()->addMonthNoOverflow()->subDay();
                $shiftModel = \App\Models\Shift::find($employee->shift_id);
                $pattern = (array) optional($shiftModel)->weekly_pattern ?: [];

                $rows = [];
                $cursor = $start->copy();
                $nowTs = now();
                $dowKeys = ['sun', 'mon', 'tue', 'wed', 'thu', 'fri', 'sat']; // Carbon: 0=Sun .. 6=Sat
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
            } catch (\Throwable $e) {
                Log::error('Failed to create shift schedule for employee ' . $employee->name . ' (id=' . $employee->id . ')', [
                    'error' => $e->getMessage(),
                ]);
                // ignore if table not present or any error
            }

            // Auto create leave balances based on employee/role and leave types
            try {
                Log::info('Leave balance creation started', [
                    'employee_id' => $employee->id,
                    'jabatan_id' => $data['jabatan_id'] ?? null,
                ]);

                $roleModel = \Spatie\Permission\Models\Role::find($data['jabatan_id'] ?? null);
                $year = now()->year;

                if (!\Illuminate\Support\Facades\Schema::hasTable('leave_types')) {
                    Log::warning('leave_types table not found; skipping leave balance creation');
                } else {
                    $annualLeaveBalance = \App\Models\EmployeeLeaveBalance::firstOrCreate(
                        [
                            'employee_id' => $employee->id,
                            'leave_type_id' => 1,
                            'year' => $year,
                        ],
                        [
                            'total_quota' => (int) $data['leave_quota_per_year'],
                            'used_quota' => 0,
                            'remaining_quota' => (int) $data['leave_quota_per_year'],
                        ]
                    );


                    $leaveType = LeaveType::where('category', LeaveTypeCategoryEnum::SPECIAL_LEAVE)->get();
                    foreach ($leaveType as $type) {
                        $employeeLeaveBalance = EmployeeLeaveBalance::firstOrCreate(
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
                }
            } catch (\Throwable $e) {
                // ignore if tables/columns not present
                Log::error('Failed to create leave balances for employee ' . $employee->name . ' (id=' . $employee->id . ')', [
                    'error' => $e->getMessage(),
                ]);
            }

            // Auto create receivable balance based on employee/role loan quota
            try {
                Log::info('Receivable balance creation started', [
                    'employee_id' => $employee->id,
                    'jabatan_id' => $data['jabatan_id'] ?? null,
                ]);
                $roleModel = \Spatie\Permission\Models\Role::find($data['jabatan_id'] ?? null);
                $empLoan = (int) ($data['loan_quota'] ?? 0);
                $roleLoan = (int) (optional($roleModel)->loan_quota ?? 0);
                $limit = $empLoan > 0 ? $empLoan : $roleLoan;
                $rec = \App\Models\EmployeeReceivableBalance::firstOrCreate(
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
                Log::info('Receivable balance ensured', [
                    'employee_id' => $employee->id,
                    'limit' => $limit,
                    'created' => $rec->wasRecentlyCreated ?? null,
                ]);
            } catch (\Throwable $e) {
                // ignore if tables not present

                Log::error('Failed to create receivable balance for employee ' . $employee->name . ' (id=' . $employee->id . ')', [
                    'error' => $e->getMessage(),
                ]);
            }

            DB::commit();

            if ($data['is_add_more']) {
                return redirect()
                    ->back()
                    ->with('success', 'Karyawan berhasil ditambahkan. Silakan tambahkan karyawan baru.');
            } else {
                return redirect()
                    ->route('employees.index')
                    ->with('success', 'Karyawan berhasil ditambahkan!');
            }
        } catch (\Illuminate\Validation\ValidationException $e) {
            return back()
                ->withErrors($e->validator->errors())
                ->withInput();
        } catch (\Throwable $e) {
            DB::rollBack();
            report($e);

            return back()
                ->withErrors(['error' => $e->getMessage()])
                ->withInput();
        }
    }


    /**
     * Display the specified resource.
     */
    public function show(Employee $employee)
    {
        abort_unless(Gate::allows('employees.view'), 403, 'Anda tidak memiliki akses untuk melihat detail karyawan');
        $employee->load(['user.roles', 'branch', 'department', 'shift', 'role', 'position', 'annualLeaveBalance', 'receivableBalance']);

        if ($employee->path) {
            $employee->photopath = asset('storage/' . ltrim($employee->path, '/'));
        } else {
            $employee->photopath = null;
        }
        // Attempt to load signature image from employee_documents (by common titles)
        try {
            $sigDoc = \App\Models\EmployeeDocument::query()
                ->where('employee_id', $employee->id)
                ->whereIn('title', ['Tanda Tangan', 'Signature', 'TTD'])
                ->latest('id')
                ->first();
            $employee->signature_url = $sigDoc && $sigDoc->file_path
                ? asset('storage/' . ltrim($sigDoc->file_path, '/'))
                : null;
        } catch (\Throwable $e) {
            $employee->signature_url = null;
        }
        // Build datasets

        $dates = $employee->attendanceShiftWorks()
            ->whereMonth('work_date', now()->month)
            ->whereYear('work_date', now()->year)
            ->where('work_date', '<=', now())
            ->pluck('work_date')
            ->map(fn($date) => (string)$date)
            ->unique()
            ->values();

        // Ambil attendance berdasarkan tanggal
        $attRaw = Attendance::query()
            ->where('employee_id', $employee->id)
            ->whereIn('tanggal', $dates)
            ->get(['tanggal', 'jam_masuk', 'jam_keluar', 'status']);

        // Index attendance by tanggal
        $attIndexed = $attRaw->keyBy(function ($r) {
            return (string)$r->tanggal;
        });

        $absenCount = 0;
        // Gabungkan seluruh tanggal shift, jika tidak ada attendance, status = 'ABSEN'
        $att = collect($dates)->map(function ($date) use ($attIndexed, &$absenCount) {
            if ($attIndexed->has($date)) {
                $r = $attIndexed->get($date);
                return [
                    'Tanggal' => $r->tanggal,
                    'Jam Masuk' => $r->jam_masuk,
                    'Jam Keluar' => $r->jam_keluar,
                    'Status' => $r->status,
                ];
            } else {
                $absenCount++;
                return [
                    'Tanggal' => $date,
                    'Jam Masuk' => null,
                    'Jam Keluar' => null,
                    'Status' => 'ABSEN',
                ];
            }
        })->sortByDesc('Tanggal')->values()->take(50);

        // Leave balance summary (current year)
        try {
            $yearNow = now()->year;
            $lbQuery = \App\Models\EmployeeLeaveBalance::query()
                ->where('employee_id', $employee->id)
                ->where('year', $yearNow);
            $balances = $lbQuery->get(['leave_type_id', 'total_quota', 'used_quota', 'remaining_quota']);
            $leaveSummary = [
                'year' => $yearNow,
                'total_quota' => (int) $balances->sum('total_quota'),
                'used_quota' => (int) $balances->sum('used_quota'),
                'remaining_quota' => (int) $balances->sum('remaining_quota'),
            ];
            // Annual leave (leave_type_id = 1 as per current seeding), fallback to first balance
            $annual = $balances->firstWhere('leave_type_id', 1) ?: $balances->first();
            $leaveSummary['annual'] = $annual ? [
                'leave_type_id' => (int) $annual->leave_type_id,
                'total_quota' => (int) $annual->total_quota,
                'used_quota' => (int) $annual->used_quota,
                'remaining_quota' => (int) $annual->remaining_quota,
            ] : null;
        } catch (\Throwable $e) {
            $leaveSummary = [
                'year' => now()->year,
                'total_quota' => 0,
                'used_quota' => 0,
                'remaining_quota' => 0,
                'annual' => null,
            ];
        }

        // KPI
        // Perbaikan query agar operator logika OR/AND tepat (bracket/closure)
        $present = Attendance::where('employee_id', $employee->id)
            ->where(function ($q) {
                $q->where('status', 'like', '%COMPLETE%')
                    ->orWhere('status', 'like', '%RUNNING%');
            })
            ->whereMonth('tanggal', now()->month)
            ->whereYear('tanggal', now()->year)
            ->where('tanggal', '<=', now())
            ->count();

        $late = Attendance::where('employee_id', $employee->id)
            ->whereNotNull('late_duration_minutes')
            ->whereMonth('tanggal', now()->month)
            ->whereYear('tanggal', now()->year)
            ->where('tanggal', '<=', now())
            ->count();

        $leave = Attendance::where('employee_id', $employee->id)
            ->where('status', 'like', '%CUTI%')
            ->whereMonth('tanggal', now()->month)
            ->whereYear('tanggal', now()->year)
            ->where('tanggal', '<=', now())
            ->count();

        $sick = Attendance::where('employee_id', $employee->id)
            ->where('status', 'like', '%SAKIT%')
            ->whereMonth('tanggal', now()->month)
            ->whereYear('tanggal', now()->year)
            ->where('tanggal', '<=', now())
            ->count();
        $ontime = $present - $late;

        $alpha = $absenCount;

        $totalDates = $dates->count() > 0 ? $dates->count() : 1; // prevent division by zero

        // Hitung cuti khusus (special leave) yang disetujui di bulan tersebut
        $currentMonth = now()->month;
        $currentYear = now()->year;
        $currentDate = now();

        $specialLeaveDays = EmployeeLeaveRequest::where('employee_id', $employee->id)
            ->where('status', 'approved')
            ->whereHas('leaveType', function ($q) {
                $q->where('category', 'special_leave');
            })
            ->where(function ($q) use ($currentMonth, $currentYear, $currentDate) {
                $q->where(function ($subQ) use ($currentMonth, $currentYear, $currentDate) {
                    // Leave request yang start_date atau end_date ada di bulan ini
                    $subQ->where(function ($dateQ) use ($currentMonth, $currentYear) {
                        $dateQ->whereMonth('start_date', $currentMonth)
                            ->whereYear('start_date', $currentYear);
                    })
                        ->orWhere(function ($dateQ) use ($currentMonth, $currentYear) {
                            $dateQ->whereMonth('end_date', $currentMonth)
                                ->whereYear('end_date', $currentYear);
                        })
                        ->orWhere(function ($dateQ) use ($currentMonth, $currentYear) {
                            // Leave request yang span bulan ini
                            $dateQ->where('start_date', '<=', $currentYear . '-' . str_pad($currentMonth, 2, '0', STR_PAD_LEFT) . '-01')
                                ->where('end_date', '>=', $currentYear . '-' . str_pad($currentMonth, 2, '0', STR_PAD_LEFT) . '-' . \Carbon\Carbon::create($currentYear, $currentMonth)->daysInMonth);
                        });
                })
                    ->where('start_date', '<=', $currentDate); // Hanya yang sudah dimulai
            })
            ->get()
            ->sum(function ($leaveRequest) use ($currentMonth, $currentYear, $currentDate, $dates) {
                // Hitung hari cuti yang overlap dengan bulan ini
                $startDate = \Carbon\Carbon::parse($leaveRequest->start_date);
                $endDate = \Carbon\Carbon::parse($leaveRequest->end_date);
                $monthStart = \Carbon\Carbon::create($currentYear, $currentMonth, 1);
                $monthEnd = \Carbon\Carbon::create($currentYear, $currentMonth, \Carbon\Carbon::create($currentYear, $currentMonth)->daysInMonth);
                $currentDateCarbon = \Carbon\Carbon::parse($currentDate);

                // Batasi sampai tanggal hari ini
                if ($endDate->gt($currentDateCarbon)) {
                    $endDate = $currentDateCarbon;
                }

                // Hitung overlap
                $overlapStart = max($startDate, $monthStart);
                $overlapEnd = min($endDate, $monthEnd);

                if ($overlapStart->lte($overlapEnd)) {
                    // Hitung hari kerja yang overlap (hanya hari yang ada shiftnya)
                    $overlapDays = 0;
                    $tempDate = $overlapStart->copy();
                    while ($tempDate->lte($overlapEnd)) {
                        // Cek apakah tanggal ini ada di $dates (ada shiftnya)
                        if ($dates->contains($tempDate->format('Y-m-d'))) {
                            $overlapDays++;
                        }
                        $tempDate->addDay();
                    }
                    return $overlapDays;
                }
                return 0;
            });

        // Hitung cuti tahunan (annual leave) yang disetujui di bulan tersebut
        $annualLeaveDays = EmployeeLeaveRequest::where('employee_id', $employee->id)
            ->where('status', 'approved')
            ->whereHas('leaveType', function ($q) {
                $q->where('category', 'annual_leave');
            })
            ->where(function ($q) use ($currentMonth, $currentYear, $currentDate) {
                $q->where(function ($subQ) use ($currentMonth, $currentYear, $currentDate) {
                    // Leave request yang start_date atau end_date ada di bulan ini
                    $subQ->where(function ($dateQ) use ($currentMonth, $currentYear) {
                        $dateQ->whereMonth('start_date', $currentMonth)
                            ->whereYear('start_date', $currentYear);
                    })
                        ->orWhere(function ($dateQ) use ($currentMonth, $currentYear) {
                            $dateQ->whereMonth('end_date', $currentMonth)
                                ->whereYear('end_date', $currentYear);
                        })
                        ->orWhere(function ($dateQ) use ($currentMonth, $currentYear) {
                            // Leave request yang span bulan ini
                            $dateQ->where('start_date', '<=', $currentYear . '-' . str_pad($currentMonth, 2, '0', STR_PAD_LEFT) . '-01')
                                ->where('end_date', '>=', $currentYear . '-' . str_pad($currentMonth, 2, '0', STR_PAD_LEFT) . '-' . \Carbon\Carbon::create($currentYear, $currentMonth)->daysInMonth);
                        });
                })
                    ->where('start_date', '<=', $currentDate); // Hanya yang sudah dimulai
            })
            ->get()
            ->sum(function ($leaveRequest) use ($currentMonth, $currentYear, $currentDate, $dates) {
                // Hitung hari cuti yang overlap dengan bulan ini
                $startDate = \Carbon\Carbon::parse($leaveRequest->start_date);
                $endDate = \Carbon\Carbon::parse($leaveRequest->end_date);
                $monthStart = \Carbon\Carbon::create($currentYear, $currentMonth, 1);
                $monthEnd = \Carbon\Carbon::create($currentYear, $currentMonth, \Carbon\Carbon::create($currentYear, $currentMonth)->daysInMonth);
                $currentDateCarbon = \Carbon\Carbon::parse($currentDate);

                // Batasi sampai tanggal hari ini
                if ($endDate->gt($currentDateCarbon)) {
                    $endDate = $currentDateCarbon;
                }

                // Hitung overlap
                $overlapStart = max($startDate, $monthStart);
                $overlapEnd = min($endDate, $monthEnd);

                if ($overlapStart->lte($overlapEnd)) {
                    // Hitung hari kerja yang overlap (hanya hari yang ada shiftnya)
                    $overlapDays = 0;
                    $tempDate = $overlapStart->copy();
                    while ($tempDate->lte($overlapEnd)) {
                        // Cek apakah tanggal ini ada di $dates (ada shiftnya)
                        if ($dates->contains($tempDate->format('Y-m-d'))) {
                            $overlapDays++;
                        }
                        $tempDate->addDay();
                    }
                    return $overlapDays;
                }
                return 0;
            });

        // Rumus Skor Kehadiran: (Jumlah Hari Hadir + Cuti Sakit + Cuti Khusus + Cuti Tahunan yang disetujui) / Jumlah Hari Yang Telah dilalui tetapi yang ada shiftnya saja * 100
        $numeratorKehadiran = $present + $sick + $specialLeaveDays + $annualLeaveDays;
        $presentaseScorePresensi = $totalDates > 0 ? round(($numeratorKehadiran / $totalDates) * 100, 1) : 0;

        // Rumus Skor Tepat Waktu: Jumlah Hari Hadir Tepat Waktu / Jumlah Hari Hadir * 100
        $presentaseScoreOntime = $present > 0 ? round(($ontime / $present) * 100, 1) : 0;

        // Rumus Skor Keseluruhan: (50% Skor Kehadiran + 50% Skor Tepat Waktu)
        $presentaseAbsenMonth = round((0.5 * $presentaseScorePresensi) + (0.5 * $presentaseScoreOntime), 1);

        $leaves = EmployeeLeaveRequest::query()
            ->where('employee_id', $employee->id)
            ->orderByDesc('created_at')
            ->limit(50)
            ->with('leaveType')
            ->get(['leave_type_id', 'start_date', 'end_date', 'total_days', 'status'])
            ->map(function ($r) {
                $status = $r->status;
                // Safely extract enum/string value
                if (is_object($status)) {
                    if (property_exists($status, 'value')) {
                        $statusStr = $status->value;
                    } elseif (method_exists($status, 'value')) {
                        $statusStr = $status->value();
                    } elseif (property_exists($status, 'name')) {
                        $statusStr = $status->name;
                    } elseif (method_exists($status, 'name')) {
                        $statusStr = $status->name();
                    } else {
                        $statusStr = (string) json_encode($status);
                    }
                } else {
                    $statusStr = (string) $status;
                }

                return [
                    'Tanggal' => Carbon::parse($r->start_date)->format('Y-m-d') . ' s/d ' . Carbon::parse($r->end_date)->format('Y-m-d'),
                    'Jenis' => $r->leaveType->name,
                    'Durasi' => $r->total_days . ' hari',
                    'Status' => $statusStr,
                ];
            });

        $overtimes = EmployeeOvertime::query()
            ->where('employee_id', $employee->id)
            ->orderByDesc('created_at')
            ->limit(50)
            ->get(['date', 'start_time', 'end_time', 'duration_hours', 'reason', 'status'])
            ->map(function ($r) {
                $convertedDuration = (int) $r->duration_hours * 60;
                return [
                    'Tanggal' => $r->date,
                    'Jam' => $r->start_time . ' - ' . $r->end_time,
                    'Durasi' => $convertedDuration . ' Menit',
                    'Keterangan' => $r->reason,
                ];
            });

        // Salary slips (payrolls)
        $payrolls = \App\Models\SalarySlip::query()
            ->where('employee_id', $employee->id)
            ->orderByDesc('bulan')
            ->limit(50)
            ->get(['bulan', 'file_url', 'created_at'])
            ->map(function ($r, $i) {
                return [
                    'No' => $i + 1,
                    'Periode' => $r->bulan,
                    'Tanggal Terbit' => optional($r->created_at)->toDateString(),
                    'Aksi' => $r->file_url,
                ];
            });

        // Shift schedules
        $schedules = \App\Models\AttendanceShiftWork::query()
            ->where('employee_id', $employee->id)
            ->orderByDesc('work_date')
            ->limit(50)
            ->with('shift:id,name')
            ->get(['work_date', 'shift_id'])
            ->map(function ($r) {
                return [
                    'Tanggal' => $r->work_date,
                    'Shift' => $r->shift->name ?? '-',
                    'Keterangan' => '-',
                ];
            });

        // Receivables (piutang)
        $debts = \App\Models\Receivable::query()
            ->where('request_by', $employee->id)
            ->orderByDesc('date')
            ->limit(50)
            ->get(['date', 'amount', 'note', 'status'])
            ->map(function ($r) {
                $status = $r->status;
                $statusStr = is_object($status)
                    ? (property_exists($status, 'value') ? $status->value : (property_exists($status, 'name') ? $status->name : (string) json_encode($status)))
                    : (string) $status;
                return [
                    'Tanggal' => $r->date,
                    'Nominal' => number_format((float) $r->amount, 2, ',', '.'),
                    'Keterangan' => $r->note,
                    'Status' => $statusStr,
                ];
            });

        // Log activities (table: log_activies with columns users_id, model_type, model_id, description, created_at)
        $activities = \App\Models\LogActivity::query()
            ->where('users_id', $employee->user_id)
            ->orderByDesc('created_at')
            ->limit(50)
            ->get(['created_at', 'model_type', 'description'])
            ->map(function ($r) {
                return [
                    'Waktu' => optional($r->created_at)->toDateTimeString(),
                    'Aksi' => $r->model_type,
                    'Oleh' => '-',
                    'Catatan' => $r->description,
                ];
            });

        // Documents (from employee_documents)
        $documents = \App\Models\EmployeeDocument::query()
            ->where('employee_id', $employee->id)
            ->orderByDesc('id')
            ->limit(50)
            ->get(['title', 'file_path', 'created_at'])
            ->map(function ($r) {
                return [
                    'Nama' => $r->title,
                    'Nilai' => $r->file_path,
                    'Tanggal Upload' => optional($r->created_at)->toDateString(),
                ];
            });

        // Get all leave types for the add leave balance modal
        $leaveTypes = LeaveType::all(['id', 'name', 'category']);

        return Inertia::render('Admin/Employees/Show', [
            'employee' => $employee,
            'kpi' => [
                'present' => $present,
                'late' => $late,
                'leave' => $leave,
                'sick' => $sick,
                'alpha' => $alpha,
            ],
            'receivable' => [
                'used' => $employee->receivableBalance?->used_amount ?? 0,
                'limit' => $employee->receivableBalance?->limit_amount ?? 0,
            ],
            'kpi_series' => [10, 30, 45, 20, 60, 40, 55],
            'attendances' => $att,
            'payrolls' => $payrolls,
            'schedules' => $schedules,
            'leaves' => $leaves,
            'overtimes' => $overtimes,
            'debts' => $debts,
            'activities' => $activities,
            'documents' => $documents,
            'presentaseAbsenMonth' => $presentaseAbsenMonth,
            'presentaseScorePresensi' => $presentaseScorePresensi,
            'presentaseScoreOntime' => $presentaseScoreOntime,
            'leaveTypes' => $leaveTypes,
            'counts' => [
                'attendances' => $att->count(),
                'payrolls' => is_countable($payrolls) ? count($payrolls) : 0,
                'schedules' => is_countable($schedules) ? count($schedules) : 0,
                'leaves' => $leaves->count(),
                'overtimes' => $overtimes->count(),
                'debts' => is_countable($debts) ? count($debts) : 0,
                'activities' => is_countable($activities) ? count($activities) : 0,
                'documents' => count($documents),
            ],
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Employee $employee)
    {
        abort_unless(Gate::allows('employees.edit'), 403, 'Anda tidak memiliki akses untuk mengubah karyawan');

        $branches = Branch::all();
        $departments = Department::all();
        // $permissions = Permission::all();
        $shifts = Shift::all();
        $roles = Role::all();
        $positions = Position::all();
        $employee->load(['user', 'branch', 'department', 'shift', 'user.roles', 'documents']);

        return Inertia::render('Admin/Employees/Edit', [
            'employee' => $employee,
            'branches' => $branches,
            'departments' => $departments,
            // 'permissions' => $permissions,
            'shifts' => $shifts,
            'roles' => $roles,
            'positions' => $positions,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Employee $employee)
    {
        Log::info('Employee update request received', [
            'employee_id' => $employee->id,
            'user_id' => auth()->id(),
        ]);

        $data = $request->validate(
            [
                'username'           => 'required|string|max:255|unique:users,username,' . $employee->user->id,
                'email'              => 'required|string|email|max:255|unique:users,email,' . $employee->user->id,
                'password'           => 'nullable|string|min:8',
                'name'               => 'required|string|max:255',
                'branch_id'          => 'required|exists:branches,id',
                'department_id'      => 'required|exists:departments,id',
                'jabatan_id'         => 'required|exists:roles,id',
                'position_id'        => 'nullable|exists:positions,id',
                'shift_id'           => 'required|exists:shifts,id',
                'working_start_date' => 'required|date',
                'salary'             => 'nullable|numeric|min:1',
                'photo'              => 'nullable|image|mimes:jpeg,jpg,png|max:10240',
                // quotas (now required per business rule)
                'leave_quota_per_year' => 'required|integer|min:0',
                'loan_quota'           => 'nullable|integer|min:0',
                // new fields
                'birthdate'               => 'required|date',
                'signature'                => 'nullable|image|mimes:jpeg,jpg,png|max:10240',
                // optional profile fields
                'contact'                  => 'nullable|string|max:50',
                'address'                  => 'nullable|string',
                'provinsi'                 => 'nullable|string|max:255',
                'kota'                     => 'nullable|string|max:255',
                'kecamatan'                => 'nullable|string|max:255',
                'kelurahan'                => 'nullable|string|max:255',
                'gender'                   => 'required|string|max:20',
                'status'                   => 'required|string|max:50',
                'birthplace'               => 'nullable|string|max:100',
                'religion'                 => 'nullable|string|max:50',
                'nik'                      => 'nullable|string|max:100',
                'ktp'                      => 'nullable|string|max:100',
                'bpjs_kesehatan'           => 'nullable|string|max:100',
                'bpjs_ketenagakerjaan'     => 'nullable|string|max:100',
                'certificate'              => 'nullable|string',
                'contract'                 => 'nullable|string',
                // document uploads or links
                'ktp_file'                 => 'nullable|file|mimes:jpeg,jpg,png,pdf|max:10240',
                'ktp_url'                  => 'nullable|url',
                'bpjs_kesehatan_file'      => 'nullable|file|mimes:jpeg,jpg,png,pdf|max:10240',
                'bpjs_kesehatan_url'       => 'nullable|url',
                'bpjs_ketenagakerjaan_file' => 'nullable|file|mimes:jpeg,jpg,png,pdf|max:10240',
                'bpjs_ketenagakerjaan_url' => 'nullable|url',
                // certificate & contract uploads
                'certificate_file'         => 'nullable|file|mimes:jpeg,jpg,png,pdf,doc,docx|max:10240',
                'certificate_url'          => 'nullable|url',
                'contract_file'            => 'nullable|file|mimes:jpeg,jpg,png,pdf,doc,docx|max:10240',
                'contract_url'             => 'nullable|url',
                'kk_file'                  => 'nullable|file|mimes:jpeg,jpg,png,pdf|max:10240',
                'kk_url'                   => 'nullable|url',
                'resign_date'              => 'nullable|date',
            ],
            [
                'required'     => ':attribute wajib diisi.',
                'unique'       => ':attribute sudah digunakan.',
                'exists'       => ':attribute tidak ditemukan.',
                'email'        => 'Format :attribute tidak valid.',
                'date'         => 'Format :attribute tidak valid.',
                'numeric'      => ':attribute harus berupa angka.',
                'integer'      => ':attribute harus berupa angka bulat.',
                'min.string'   => ':attribute minimal :min karakter.',
                'min.numeric'  => ':attribute minimal :min.',
                'min.integer'  => ':attribute minimal :min.',
                'max.string'   => ':attribute maksimal :max karakter.',
                'max.numeric'  => ':attribute maksimal :max.',
                'image'        => 'File :attribute harus berupa gambar.',
                'file'         => 'File :attribute tidak valid.',
                'mimes'        => ':attribute harus berformat: :values.',
                'max.file'     => 'Ukuran :attribute maksimal :max KB.',
                'photo.max'    => 'Ukuran foto maksimal 10MB.',
                'signature.max' => 'Ukuran tanda tangan maksimal 10MB.',
                'salary.min'   => 'Gaji pokok minimal lebih dari 0.',
                'leave_quota_per_year.required' => 'Jatah cuti per tahun wajib diisi.',
                'leave_quota_per_year.min' => 'Jatah cuti per tahun minimal 0 hari.',
                'birthdate.required' => 'Tanggal lahir wajib diisi.',
                'gender.required' => 'Jenis kelamin wajib dipilih.',
                'status.required' => 'Status karyawan wajib dipilih.',
            ],
            [
                'username'            => 'Username',
                'email'               => 'Email',
                'password'            => 'Password',
                'name'                => 'Nama',
                'branch_id'           => 'Cabang',
                'department_id'       => 'Departemen',
                'jabatan_id'          => 'Jabatan',
                'position_id'         => 'Position',
                // 'permission_id'       => 'Permission',
                'shift_id'            => 'Shift',
                'working_start_date'  => 'Tanggal mulai kerja',
                'salary'              => 'Gaji',
                'photo'               => 'Foto',
                'birthdate'           => 'Tanggal Lahir',
                'signature'           => 'Tanda Tangan',
                'ktp_file'            => 'File KTP',
                'ktp_url'             => 'Link KTP',
                'bpjs_kesehatan_file' => 'File BPJS Kesehatan',
                'bpjs_kesehatan_url'  => 'Link BPJS Kesehatan',
                'bpjs_ketenagakerjaan_file' => 'File BPJS Ketenagakerjaan',
                'bpjs_ketenagakerjaan_url'  => 'Link BPJS Ketenagakerjaan',
                'certificate_file'          => 'File Sertifikat',
                'certificate_url'           => 'Link Sertifikat',
                'contract_file'             => 'File Kontrak',
                'contract_url'              => 'Link Kontrak',
                'kk_file'                   => 'File Kartu Keluarga',
                'kk_url'                    => 'Link Kartu Keluarga',
                'resign_date'               => 'Tanggal Resign',
                'leave_quota_per_year'      => 'Jatah Cuti per Tahun',
                'loan_quota'                => 'Jatah Piutang',
                'contact'                   => 'No. Telepon',
                'address'                   => 'Alamat',
                'gender'                    => 'Jenis Kelamin',
                'status'                    => 'Status Karyawan',
                'birthplace'                => 'Tempat Lahir',
                'religion'                  => 'Agama',
                'nik'                       => 'NIK',
                'ktp'                       => 'KTP',
                'bpjs_kesehatan'            => 'BPJS Kesehatan',
                'bpjs_ketenagakerjaan'      => 'BPJS Ketenagakerjaan',
                'certificate'               => 'Sertifikat',
                'contract'                  => 'Kontrak',
            ]
        );

        try {
            Log::info('Updating employee', [
                'employee_id' => $employee->id,
                'username' => $data['username'],
                'name' => $data['name'],
                'email' => $data['email'],
                'jabatan_id' => $data['jabatan_id'] ?? null,
                'position_id' => $data['position_id'] ?? null,
            ]);

            DB::beginTransaction();

            // Upload employee photo using trait FIRST (with old file deletion)
            $photoData = $this->uploadEmployeePhotoWithTimestamp(
                $request,
                'photo',
                $employee->file_name,
                $employee->path
            );
            $fileName = $photoData['file_name'];
            $storedPath = $photoData['path'];

            $user = $employee->user;
            $user->name = $request->input('name');
            $user->username = $request->input('username') ?? null;
            $user->email = $request->input('email');
            if (!empty($request->input('password'))) {
                $user->password = Hash::make($request->input('password'));
            }
            // Update profile_photo_path for user profile display
            if ($storedPath) {
                $user->profile_photo_path = $storedPath;
            }
            $user->save();

            $employee->name = $request->input('name');
            $employee->branch_id = $request->input('branch_id');
            $employee->department_id = $request->input('department_id');
            // $employee->permission_id = $request->input('permission_id');
            $employee->position_id = $request->input('position_id') ?? null; // from positions table
            $employee->shift_id = $request->input('shift_id') ?? null;
            $employee->working_start_date = $request->input('working_start_date');
            $employee->salary = $request->input('salary');

            // optional quotas per employee
            if (\Illuminate\Support\Facades\Schema::hasColumn('employees', 'leave_quota_per_year')) {
                $employee->leave_quota_per_year = (int) ($data['leave_quota_per_year'] ?? $employee->leave_quota_per_year ?? 0);
            }
            if (\Illuminate\Support\Facades\Schema::hasColumn('employees', 'loan_quota')) {
                $employee->loan_quota = (int) ($data['loan_quota'] ?? $employee->loan_quota ?? 0);
            }

            // optional fields
            $employee->contact = $request->input('contact');
            $employee->address = $request->input('address');
            $employee->provinsi = $request->input('provinsi');
            $employee->kota = $request->input('kota');
            $employee->kecamatan = $request->input('kecamatan');
            $employee->kelurahan = $request->input('kelurahan');
            $employee->gender = $request->input('gender');
            $employee->status = $request->input('status');
            $employee->birthplace = $request->input('birthplace');
            $employee->religion = $request->input('religion');
            $employee->nik = $request->input('nik');
            $employee->ktp = $request->input('ktp');
            $employee->bpjs_kesehatan = $request->input('bpjs_kesehatan');
            $employee->bpjs_ketenagakerjaan = $request->input('bpjs_ketenagakerjaan');
            $employee->certificate = $request->input('certificate');
            $employee->contract = $request->input('contract');
            // Optional: resign_date only if column exists
            try {
                if (\Illuminate\Support\Facades\Schema::hasColumn('employees', 'resign_date')) {
                    $resignDate = $request->input('resign_date');

                    if ($resignDate) {
                        $employee->resign_date = Carbon::parse($resignDate)->format('Y-m-d');
                        $employee->status = 'Tidak Aktif';
                    } else {
                        $employee->resign_date = null;
                    }
                    Log::info('Resign date updated', [
                        'employee_id' => $employee->id,
                        'resign_date' => $employee->resign_date,
                        'input_value' => $resignDate,
                    ]);
                }
            } catch (\Throwable $e) {
                Log::error('Failed to update resign_date', [
                    'employee_id' => $employee->id,
                    'error' => $e->getMessage(),
                    'input_value' => $request->input('resign_date'),
                ]);
                throw $e; // Re-throw to see the actual error
            }

            // Optional: birthdate only if column exists
            try {
                if (\Illuminate\Support\Facades\Schema::hasColumn('employees', 'birthdate')) {
                    $employee->birthdate = $request->input('birthdate');
                }
            } catch (\Throwable $e) {
            }

            if ($fileName) {
                $employee->file_name = $fileName;
                $employee->path = $storedPath;
            }

            // If jabatan changed, sync the user's Spatie role to match
            try {
                if (isset($data['jabatan_id'])) {
                    $roleModel = \Spatie\Permission\Models\Role::find((int) $data['jabatan_id']);
                    if ($roleModel && $employee->user) {
                        $employee->user->syncRoles([$roleModel->name]);
                    }
                }
            } catch (\Throwable $e) {
                Log::error('Failed syncing user role on employee update', [
                    'employee_id' => $employee->id,
                    'error' => $e->getMessage(),
                ]);
            }

            $employee->save();


            $employee->leaveBalance()->updateOrCreate([
                'employee_id' => $employee->id,
                'leave_type_id' => 1,
            ], [
                'total_quota' => $employee->leave_quota_per_year,
                'remaining_quota' => $employee->leave_quota_per_year - $employee->leaveBalance()->where('leave_type_id', 1)->sum('used_quota'),
                'year' => Carbon::now()->year,
            ]);

            // Save optional identity documents using trait batch method (with update support)
            $this->saveEmployeeDocumentsBatch($request, $employee->id, [
                [
                    'title' => 'KTP',
                    'file_field' => 'ktp_file',
                    'url_field' => 'ktp_url',
                ],
                [
                    'title' => 'BPJS Kesehatan',
                    'file_field' => 'bpjs_kesehatan_file',
                    'url_field' => 'bpjs_kesehatan_url',
                ],
                [
                    'title' => 'BPJS Ketenagakerjaan',
                    'file_field' => 'bpjs_ketenagakerjaan_file',
                    'url_field' => 'bpjs_ketenagakerjaan_url',
                ],
                [
                    'title' => 'Sertifikat',
                    'file_field' => 'certificate_file',
                    'url_field' => 'certificate_url',
                ],
                [
                    'title' => 'Kontrak',
                    'file_field' => 'contract_file',
                    'url_field' => 'contract_url',
                ],
                [
                    'title' => 'Kartu Keluarga',
                    'file_field' => 'kk_file',
                    'url_field' => 'kk_url',
                ],
            ], true); // Update if exists

            // Save signature document if uploaded using trait (with update support)
            $this->uploadEmployeeSignature($request, $employee->id, 'signature', true);

            Log::info('Employee documents updated for employee ' . $employee->name . ' (id=' . $employee->id . ')');

            DB::commit();

            Log::info('Employee updated successfully', [
                'employee_id' => $employee->id,
            ]);

            return redirect()
                ->route('employees.index')
                ->with('success', 'Karyawan berhasil diupdate!');
        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error('Employee update validation failed', [
                'employee_id' => $employee->id,
                'errors' => $e->validator->errors()->toArray(),
            ]);
            return back()
                ->withErrors($e->validator->errors())
                ->withInput();
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Employee update failed', [
                'employee_id' => $employee->id,
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);
            report($e);

            return back()
                ->withErrors(['error' => 'Gagal memperbarui data karyawan: ' . $e->getMessage()])
                ->withInput();
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(String $id)
    {
        $employee = Employee::findOrFail($id);

        // Check if employee has blocking relationships
        // Blocking relationships: vehicles, attendances, inspections
        $blockingRelationships = [];

        // Check attendances
        if ($employee->attendances()->exists()) {
            $blockingRelationships[] = 'kehadiran';
        }

        // Check vehicles
        if ($employee->vehicles()->exists()) {
            $blockingRelationships[] = 'kendaraan';
        }

        // Check inspections (polymorphic relationship)
        $inspectionCount = \App\Models\Inspection::where('model_type', 'App\Models\Employee')
            ->where('model_id', $employee->id)
            ->count();

        if ($inspectionCount > 0) {
            $blockingRelationships[] = 'inspection';
        }

        // Also check through answers relationship
        if ($employee->answers()->whereNotNull('inspection_id')->exists()) {
            if (!in_array('inspection', $blockingRelationships)) {
                $blockingRelationships[] = 'inspection';
            }
        }

        if (!empty($blockingRelationships)) {
            $message = 'Data karyawan tidak dapat dihapus karena masih memiliki relasi dengan data: ' . implode(', ', $blockingRelationships);
            session()->flash('error', $message);
            return redirect()->back();
        }

        // If no blocking relationships, proceed with deletion
        // First delete the associated user if exists
        if ($employee->user_id) {
            $user = $employee->user;
            if ($user) {
                $user->delete();
            }
        }

        // Then delete the employee
        $employee->delete();

        session()->flash('OK', 'Karyawan berhasil dihapus!');
        return redirect()->back();
    }

    /**
     * Bulk update department/branch/status for selected employees
     */
    public function bulkUpdate(Request $request)
    {
        $data = $request->validate([
            'ids' => ['required', 'array', 'min:1'],
            'ids.*' => ['integer', 'exists:employees,id'],
            'department_id' => ['nullable', 'integer', 'exists:departments,id'],
            'branch_id' => ['nullable', 'integer', 'exists:branches,id'],
            'status' => ['nullable', 'string', 'max:50'],
        ]);

        $updates = [];
        if (isset($data['department_id']) && $data['department_id']) $updates['department_id'] = (int) $data['department_id'];
        if (isset($data['branch_id']) && $data['branch_id']) $updates['branch_id'] = (int) $data['branch_id'];
        if (array_key_exists('status', $data) && $data['status'] !== null && $data['status'] !== '') $updates['status'] = $data['status'];

        if (empty($updates)) {
            return back()->with('info', 'Tidak ada perubahan untuk diterapkan.');
        }

        Employee::whereIn('id', $data['ids'])->update($updates);
        return back()->with('success', 'Perubahan berhasil diterapkan pada ' . count($data['ids']) . ' karyawan.');
    }

    /**
     * Bulk delete selected employees
     */
    public function bulkDelete(Request $request)
    {
        $data = $request->validate([
            'ids' => ['required', 'array', 'min:1'],
            'ids.*' => ['integer', 'exists:employees,id'],
        ]);

        Employee::whereIn('id', $data['ids'])->delete();
        return back()->with('success', count($data['ids']) . ' karyawan dihapus.');
    }

    public function updateVerify(Request $request, Employee $employee)
    {
        $verification = $request->input('verification', true);

        // Convert string to boolean if needed
        if (is_string($verification)) {
            $verification = filter_var($verification, FILTER_VALIDATE_BOOLEAN);
        }

        $employee->verification = $verification;
        $employee->save();

        $message = $verification
            ? 'Karyawan berhasil di verifikasi!'
            : 'Verifikasi karyawan berhasil dibatalkan!';

        // Redirect back to the employee detail page so the verification badge updates immediately
        return redirect()->route('employees.show', $employee->id)->with('Success', $message);
    }

    /**
     * Verify and update employee core identity fields via modal.
     */
    public function verifyAndUpdate(Request $request, Employee $employee)
    {
        // Basic validation for core fields before verification
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'username' => ['required', 'string', 'max:255', 'unique:users,username,' . $employee->user_id],
            'email' => ['required', 'email', 'max:255', 'unique:users,email,' . $employee->user_id],
            'contact' => ['nullable', 'string', 'max:255'],
            'address' => ['nullable', 'string'],
        ]);

        DB::beginTransaction();
        try {
            // Update linked user
            $user = $employee->user;
            if ($user) {
                $user->name = $request->input('name');
                $user->username = $request->input('username');
                $user->email = $request->input('email');
                $user->save();
            }

            // Update employee additional info
            $employee->name = $request->input('name');
            $employee->contact = $request->input('contact');
            $employee->address = $request->input('address');
            $employee->verification = true;
            $employee->save();

            DB::commit();
            return redirect()->back()->with('Success', 'Data diperbarui dan karyawan terverifikasi.');
        } catch (\Throwable $e) {
            DB::rollBack();
            return redirect()->back()->with('Error', 'Gagal memverifikasi: ' . $e->getMessage());
        }
    }

    public function exportData()
    {
        $request = request();

        $query = Employee::with(['user.roles', 'branch', 'department', 'shift', 'role', 'position'])
            ->when($request->filled('search'), function ($q) use ($request) {
                $search = $request->string('search')->toString();
                $q->where(function ($qq) use ($search) {
                    $qq->where('name', 'like', "%{$search}%")
                        ->orWhereHas('user', function ($uq) use ($search) {
                            $uq->where('username', 'like', "%{$search}%");
                        });
                });
            })
            ->when($request->filled('name'), function ($q) use ($request) {
                $term = $request->string('name')->toString();
                $q->where(function ($qq) use ($term) {
                    $qq->where('name', 'like', "%{$term}%")
                        ->orWhereHas('user', function ($uq) use ($term) {
                            $uq->where('username', 'like', "%{$term}%");
                        });
                });
            })
            ->when($request->filled('branch_id'), function ($q) use ($request) {
                $q->where('branch_id', (int) $request->branch_id);
            })
            ->when($request->filled('department_id'), function ($q) use ($request) {
                $q->where('department_id', (int) $request->department_id);
            })
            ->when($request->filled('gender'), function ($q) use ($request) {
                $gender = $request->string('gender')->toString();
                $q->where('gender', 'like', "%{$gender}%");
            })
            ->when($request->filled('status'), function ($q) use ($request) {
                $q->where('status', $request->string('status')->toString());
            })
            ->when($request->filled('shift_id'), function ($q) use ($request) {
                $q->where('shift_id', (int) $request->shift_id);
            })
            ->when($request->filled('role_id'), function ($q) use ($request) {
                $rid = (int) $request->role_id;
                $q->whereHas('user.roles', function ($rq) use ($rid) {
                    $rq->where('id', $rid);
                });
            })
            ->when($request->filled('position_id'), function ($q) use ($request) {
                $q->where('position_id', (int) $request->position_id);
            });

        $employees = $query->orderBy('name')->get();

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        $headers = ['No', 'Name', 'Username', 'Email', 'Branch', 'Department', 'Position/Jabatan', 'Shift', 'Working Start Date', 'Salary'];
        $sheet->fromArray($headers, null, 'A1');

        $row = 2;
        foreach ($employees as $idx => $employee) {
            $sheet->fromArray([
                $idx + 1,
                $employee->name ?? '-',
                optional($employee->user)->username ?? '-',
                optional($employee->user)->email ?? '-',
                optional($employee->branch)->name ?? '-',
                optional($employee->department)->name ?? '-',
                optional($employee->role)->name ?? (optional(optional($employee->user)->roles)->first()->name ?? '-'),
                optional($employee->shift)->name ?? '-',
                $employee->working_start_date ?? '-',
                $employee->salary ?? '-',
            ], null, 'A' . $row);
            $row++;
        }

        foreach (range('A', $sheet->getHighestColumn()) as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $filename = 'employees_data_' . date('Ymd_His') . '.xlsx';
        $writer = new Xlsx($spreadsheet);
        $writer->save($filename);

        return response()->download($filename)->deleteFileAfterSend(true);
    }
}
