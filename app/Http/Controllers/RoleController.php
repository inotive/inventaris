<?php

namespace App\Http\Controllers;

use Throwable;
use App\Models\Role;
use App\Models\User;
use Inertia\Inertia;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Spatie\Permission\Models\Permission;

class RoleController extends Controller
{
    protected $role;
    protected $user;

    public function __construct(Role $role, User $user)
    {
        $this->role = $role;
        $this->user = $user;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        abort_unless(Gate::allows('roles.view'), 403, 'Anda tidak memiliki akses untuk melihat data jabatan');

        $sortBy = $request->get('sortBy', 'created_at');
        $sortDirection = $request->get('sortDirection', 'desc');

        $search = $request->input('search');
        $perPage = $request->input('per_page', 10);

        $roles = Role::withCount(['users as total', 'permissions as access'])
            ->when($search, function ($query, $search) {
                $query->where('name', 'like', "%{$search}%");
            })
            ->orderBy($sortBy, $sortDirection)
            ->paginate($perPage)
            ->withQueryString();

        return Inertia::render('Admin/Roles/Index', compact('roles', 'sortBy', 'sortDirection', 'search'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        abort_unless(Gate::allows('roles.create'), 403, 'Anda tidak memiliki akses untuk menambah jabatan');
        
        // Method ini tidak digunakan, tapi tetap dilindungi
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        abort_unless(Gate::allows('roles.create'), 403, 'Anda tidak memiliki akses untuk menambah jabatan');

        $data = $request->validate([
            'name' => ['required','string','max:255'],
            'leave_quota_per_year' => ['nullable','integer','min:0'],
            'loan_quota' => ['nullable','numeric','min:0'],
        ]);

        $payload = [
            'name' => $data['name'],
            'leave_quota_per_year' => $data['leave_quota_per_year'] ?? 0,
            'loan_quota' => $data['loan_quota'] ?? 0,
        ];

        Role::create($payload);

        return redirect()->back()->with('success', 'Peran berhasil ditambahkan');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        abort_unless(Gate::allows('roles.view'), 403, 'Anda tidak memiliki akses untuk melihat detail jabatan');
        
        // Method ini tidak digunakan, tapi tetap dilindungi
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Role $role)
    {
        abort_unless(Gate::allows('roles.edit'), 403, 'Anda tidak memiliki akses untuk mengubah jabatan');

        $permissions = Permission::all()->groupBy('group_name');

        return Inertia::render('Admin/Roles/Edit', [
            'role' => $role->load('permissions'),
            'permissions' => $permissions,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Role $role)
    {
        abort_unless(Gate::allows('roles.edit'), 403, 'Anda tidak memiliki akses untuk mengubah jabatan');

        // Branch by intent: if permission_ids present, handle permission toggle; else update role fields
        if ($request->has('permission_ids')) {
            $validated = $request->validate([
                'permission_ids' => 'required|array',
                'permission_ids.*' => 'integer|exists:permissions,id',
                'checked' => 'required|boolean',
            ]);

            $permissionIds = $validated['permission_ids'];
            $checked = $validated['checked'];

            $permissions = \Spatie\Permission\Models\Permission::whereIn('id', $permissionIds)->get();

            if ($checked) {
                $role->givePermissionTo($permissions);
            } else {
                foreach ($permissions as $permission) {
                    $role->revokePermissionTo($permission);
                }
            }

            app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

            return back()->with('success', 'Hak akses berhasil diperbarui');
        }

        // Otherwise update basic fields (name + quotas)
        $data = $request->validate([
            'name' => ['required','string','max:255'],
            'leave_quota_per_year' => ['nullable','integer','min:0'],
            'loan_quota' => ['nullable','numeric','min:0'],
        ]);

        $role->update([
            'name' => $data['name'],
            'leave_quota_per_year' => $data['leave_quota_per_year'] ?? ($role->leave_quota_per_year ?? 0),
            'loan_quota' => $data['loan_quota'] ?? ($role->loan_quota ?? 0),
        ]);

        return back()->with('success', 'Jabatan berhasil diperbarui');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Role $role)
    {
        abort_unless(Gate::allows('roles.delete'), 403, 'Anda tidak memiliki akses untuk menghapus jabatan');

        try {
            if ($role->users()->exists()) {
                return redirect()->back()->with('error', 'Peran memiliki pengguna.');
            }

            $role->delete();

            return redirect()->back()->with('success', 'Peran berhasil dihapus');
        } catch (Throwable $e) {
            return redirect()->back()->with('error', 'Terjadi kesalahan saat menghapus peran: ' . $e->getMessage());
        }
    }

    /**
     * Return users and permissions for a Role (for modal stats)
     */
    public function stats(Role $role)
    {
        abort_unless(Gate::allows('roles.view'), 403, 'Anda tidak memiliki akses untuk melihat statistik jabatan');

        $users = $role->users()
            ->select('id', 'name', 'email', 'username')
            ->orderBy('name')
            ->get();

        $permissions = $role->permissions()
            ->select('id', 'name', 'display_name', 'group_name')
            ->orderBy('group_name')
            ->orderBy('name')
            ->get();

        return response()->json([
            'role' => [
                'id' => $role->id,
                'name' => $role->name,
            ],
            'users' => $users,
            'permissions' => $permissions,
        ]);
    }
}