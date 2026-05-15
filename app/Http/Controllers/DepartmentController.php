<?php

namespace App\Http\Controllers;

use App\Models\Department;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;

class DepartmentController extends Controller
{
    public function indexPrime(Request $request)
    {
        abort_unless(Gate::allows('departments.view'), 403, 'Anda tidak memiliki akses untuk melihat data departemen');

        $perPage = max(1, min((int) $request->get('per_page', 10), 100));
        $q = trim((string) $request->get('q', ''));
        $sortField = $request->get('sort_field', 'id');
        $sortOrder = (int) $request->get('sort_order', 1); // 1 asc, -1 desc (PrimeVue convention)

        // Whitelist sortable fields
        $allowedSorts = ['id', 'name', 'code', 'description', 'employees_count'];
        if (!in_array($sortField, $allowedSorts, true)) {
            $sortField = 'id';
        }
        $direction = $sortOrder === -1 ? 'desc' : 'asc';

        $query = Department::query();
        if ($q !== '') {
            $query->where(function ($w) use ($q) {
                $w->where('name', 'like', "%$q%")
                    ->orWhere('code', 'like', "%$q%")
                    ->orWhere('description', 'like', "%$q%");
            });
        }

        $departments = $query->withCount('employees')->orderBy($sortField, $direction)
            ->paginate($perPage)
            ->withQueryString()
            ->onEachSide(1)
            ->through(function ($d) {
                return [
                    'id' => $d->id,
                    'name' => $d->name,
                    'code' => $d->code,
                    'description' => $d->description,
                    'employees_count' => $d->employees_count,
                ];
            });

        return Inertia::render('Admin/Departments/Index', [
            'departments' => $departments,
            'filters' => [
                'q' => $q,
                'sort_field' => $sortField,
                'sort_order' => $sortOrder,
                'per_page' => $perPage,
            ],
        ]);
    }

    public function show(Department $department)
    {
        abort_unless(Gate::allows('departments.view'), 403, 'Anda tidak memiliki akses untuk melihat detail departemen');

        $department->load(['branch:id,name,region']);
        $department->loadCount('employees');

        $employees = $department->employees()
            ->where('branch_id', $department->branch_id)
            ->with(['user:id,name,email', 'branch:id,name,region', 'shift:id,code'])
            ->orderBy('id', 'asc')
            ->paginate(10)
            ->onEachSide(1)
            ->through(function ($e) {
                return [
                    'id' => $e->id,
                    'name' => $e->name,
                    'gender' => $e->gender,
                    'branch' => optional($e->branch)->name,
                    'shift' => optional($e->shift)->code,
                    'email' => optional($e->user)->email,
                    'status' => $e->status,
                ];
            });

        return Inertia::render('Admin/Departments/Show', [
            'department' => [
                'id' => $department->id,
                'name' => $department->name,
                'code' => $department->code ?? null,
                'description' => $department->description ?? null,
                'status' => (bool) ($department->status ?? false),
                'branch' => optional($department->branch)->name,
                'employees_count' => $department->employees_count,
                'created_at' => $department->created_at?->toDateTimeString(),
            ],
            'employees' => $employees,
        ]);
    }

    public function index(Request $request)
    {
        abort_unless(Gate::allows('departments.view'), 403, 'Anda tidak memiliki akses untuk melihat data departemen');

        $sortBy = $request->get('sortBy', 'created_at');
        $sortDirection = $request->get('sortDirection', 'desc');

        $search = $request->input('search');
        $perPage = $request->input('per_page', 10);

        $departments = Department::with(['branch:id,name'])
            ->withCount('employees')
            ->when($search, function ($query, $search) {
                $statusWord = strtolower(trim($search));
                $statusMap = [
                    'aktif' => true,
                    'active' => true,
                    'non aktif' => false,
                    'nonaktif' => false,
                    'inactive' => false,
                ];
                $isStatusSearch = array_key_exists($statusWord, $statusMap);

                $query->where(function ($w) use ($search, $isStatusSearch, $statusMap, $statusWord) {
                    $w->where('name', 'like', "%{$search}%")
                        ->orWhereHas('branch', function ($b) use ($search) {
                            $b->where('name', 'like', "%{$search}%");
                        });

                    if ($isStatusSearch) {
                        $w->orWhere('status', $statusMap[$statusWord]);
                    }
                });
            })
            ->when(in_array($sortBy, ['name', 'code', 'employees_count', 'id']), function ($q) use ($sortBy, $sortDirection) {
                $q->orderBy($sortBy, $sortDirection);
            }, function ($q) {
                $q->orderBy('created_at', 'desc');
            })
            ->paginate($perPage)
            ->withQueryString();

        $branches = \App\Models\Branch::select('id','name')->orderBy('name')->get();

        return Inertia::render('Admin/Departments/Index', compact('departments', 'branches', 'sortBy', 'sortDirection', 'search'));
    }

    public function create()
    {
        abort_unless(Gate::allows('departments.create'), 403, 'Anda tidak memiliki akses untuk menambah departemen');

        $branches = \App\Models\Branch::select('id','name')->orderBy('name')->get();
        return Inertia::render('Admin/Departments/Create', [
            'branches' => $branches,
        ]);
    }

    public function store(Request $request)
    {
        abort_unless(Gate::allows('departments.create'), 403, 'Anda tidak memiliki akses untuk menambah departemen');

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'branch_id' => ['required','exists:branches,id'],
            'description' => ['nullable', 'string'],
            'status' => ['nullable','boolean'],
        ]);

        try {
            DB::transaction(function () use ($validated) {
                Department::create($validated);
            });
            return back()->with('success', 'Departemen berhasil ditambahkan');
        } catch (\Throwable $e) {
            Log::error('Store Department failed', ['error' => $e->getMessage()]);
            return back()->with('error', 'Gagal menambah departemen');
        }
    }

    public function edit(Department $department)
    {
        abort_unless(Gate::allows('departments.edit'), 403, 'Anda tidak memiliki akses untuk mengubah departemen');

        $branches = \App\Models\Branch::select('id','name')->orderBy('name')->get();
        return Inertia::render('Admin/Departments/Edit', [
            'department' => $department->only(['id','name','code','description','branch_id','status']),
            'branches' => $branches,
        ]);
    }

    public function update(Request $request, Department $department)
    {
        abort_unless(Gate::allows('departments.edit'), 403, 'Anda tidak memiliki akses untuk mengubah departemen');

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'branch_id' => ['required','exists:branches,id'],
            'description' => ['nullable', 'string'],
            'status' => ['nullable','boolean'],
        ]);

        try {
            DB::transaction(function () use ($department, $validated) {
                $department->update($validated);
            });
            return back()->with('success', 'Departemen berhasil diubah');
        } catch (\Throwable $e) {
            Log::error('Update Department failed', ['error' => $e->getMessage()]);
            return back()->with('error', 'Gagal mengubah departemen');
        }
    }

    public function destroy(Department $department)
    {
        abort_unless(Gate::allows('departments.delete'), 403, 'Anda tidak memiliki akses untuk menghapus departemen');

        // Check if department has related data
        $hasEmployees = $department->employees()->exists();
        $hasChecklists = method_exists($department, 'checklists') ? $department->checklists()->exists() : false;

        if ($hasEmployees || $hasChecklists) {
            $relatedData = [];
            if ($hasEmployees) $relatedData[] = 'karyawan';
            if ($hasChecklists) $relatedData[] = 'checklist';

            $message = 'Gagal Menghapus, Data sedang digunakan ';
            return back()->with('error', $message);
        }

        try {
            DB::transaction(function () use ($department) {
                $department->delete();
            });
            return back()->with('success', 'Departemen berhasil dihapus');
        } catch (\Throwable $e) {
            Log::error('Delete Department failed', ['error' => $e->getMessage()]);
            return back()->with('error', 'Gagal menghapus departemen');
        }
    }

    public function getEmployees(Department $department)
    {
        abort_unless(Gate::allows('departments.view'), 403, 'Anda tidak memiliki akses');

        $employees = $department->employees()->get();
        return response()->json($employees);
    }
}
