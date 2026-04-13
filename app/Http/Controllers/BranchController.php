<?php

namespace App\Http\Controllers;

use Throwable;
use Inertia\Inertia;
use App\Models\Branch;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class BranchController extends Controller
{
    public function indexPrime(Request $request)
    {
        abort_unless(Gate::allows('branches.view'), 403, 'Anda tidak memiliki akses untuk melihat data cabang');

        $branches = Branch::withCount('employees')->orderBy('id')
            ->paginate(10)
            ->onEachSide(1)
            ->through(function ($b) {
                return [
                    'id' => $b->id,
                    'name' => $b->name,
                    'region' => $b->region,
                    'head_name' => $b->head_name,
                    'employees_count' => $b->employees_count,
                    'email' => $b->email,
                    'contact' => $b->contact,
                    'address' => $b->address,
                    'created_at' => $b->created_at?->toDateTimeString(),
                ];
            });

        return Inertia::render('Admin/Branches/Index', [
            'branches' => $branches,
        ]);
    }

    public function show(Request $request, Branch $branch)
    {
        abort_unless(Gate::allows('branches.view'), 403, 'Anda tidak memiliki akses untuk melihat detail cabang');

        $branch->loadCount('employees');

        $perPage = $request->input('per_page', 10);

        $employees = $branch->employees()
            ->with(['user:id,name,email', 'department:id,name', 'shift:id,code'])
            ->orderBy('id', 'asc')
            ->paginate($perPage)
            ->withQueryString()
            ->onEachSide(1)
            ->through(function($e){
                return [
                    'id' => $e->id,
                    'name' => $e->name,
                    'gender' => $e->gender,
                    'department' => optional($e->department)->name,
                    'shift' => optional($e->shift)->code,
                    'email' => optional($e->user)->email,
                    'status' => $e->status,
                ];
            });

        // Ensure all branch data is properly formatted
        $branchData = [
            'id' => $branch->id,
            'name' => $branch->name ?? '-',
            'region' => $branch->region ?? '-',
            'email' => $branch->email ?? '-',
            'phone' => $branch->contact ?? '-',
            'contact' => $branch->contact ?? '-',
            'address' => $branch->address ?? '-',
            'description' => $branch->description ?? '-',
            'employees_count' => $branch->employees_count ?? 0,
            'created_at' => $branch->created_at?->toDateTimeString(),
        ];

        return Inertia::render('Admin/Branches/Show', [
            'branch' => $branchData,
            'employees' => $employees,
        ]);
    }

    public function index(Request $request)
    {
        abort_unless(Gate::allows('branches.view'), 403, 'Anda tidak memiliki akses untuk melihat data cabang');

        $sortBy = $request->get('sortBy', 'created_at');
        $sortDirection = $request->get('sortDirection', 'desc');

        $search = $request->input('search');
        $perPage = $request->input('per_page', 10);

        $branches = Branch::withCount('employees')->when($search, function ($query, $search) {
            $query->where('name', 'like', "%{$search}%");
        })
            ->orderBy($sortBy, $sortDirection)
            ->paginate($perPage)
            ->withQueryString();

        return Inertia::render('Admin/Branches/Index', compact('branches', 'sortBy', 'sortDirection', 'search'));
    }

    public function store(Request $request)
    {
        abort_unless(Gate::allows('branches.create'), 403, 'Anda tidak memiliki akses untuk menambah cabang');

        $data = $request->validate([
            'name' => 'required|string|max:255',
            'region' => 'nullable|string|max:255',
            'head_name' => 'nullable|string|max:255',
            'employees_count' => 'nullable|integer|min:0',
            'email' => 'nullable|email|max:255',
            'contact' => 'nullable|string|max:50',
            'address' => 'nullable|string',
            'description' => 'nullable|string',
        ]);

        Branch::create($data);

        return redirect()->back()->with('success', 'Kantor Cabang berhasil ditambahkan');
    }

    public function update(Request $request, Branch $branch)
    {
        abort_unless(Gate::allows('branches.edit'), 403, 'Anda tidak memiliki akses untuk mengubah cabang');

        $data = $request->validate([
            'name' => 'required|string|max:255',
            'region' => 'nullable|string|max:255',
            'head_name' => 'nullable|string|max:255',
            'employees_count' => 'nullable|integer|min:0',
            'email' => 'nullable|email|max:255',
            'contact' => 'nullable|string|max:50',
            'address' => 'nullable|string',
            'description' => 'nullable|string',
        ]);

        $branch->update($data);

        return redirect()->back()->with('success', 'Kantor Cabang berhasil diperbarui');
    }

    public function destroy(Branch $branch)
    {
        abort_unless(Gate::allows('branches.delete'), 403, 'Anda tidak memiliki akses untuk menghapus cabang');

        try {
            if (method_exists($branch, 'departments') && $branch->departments()->exists()) {
                return redirect()->back()->with('error', 'Kantor tidak bisa dihapus karena masih memiliki data departemen.');
            }

            if (method_exists($branch, 'employees') && $branch->employees()->exists()) {
                return redirect()->back()->with('error', 'Kantor tidak bisa dihapus karena masih memiliki data karyawan.');
            }

            if (method_exists($branch, 'items') && $branch->items()->exists()) {
                return redirect()->back()->with('error', 'Kantor tidak bisa dihapus karena masih memiliki data barang.');
            }

            $branch->delete();

            return redirect()->back()->with('success', 'Kantor berhasil dihapus');
        } catch (\Illuminate\Database\QueryException $e) {
            $sqlMessage = $e->getMessage();
            \Log::error('Branch delete SQL error', [
                'message' => $sqlMessage,
                'sql' => method_exists($e, 'getSql') ? $e->getSql() : null,
                'bindings' => method_exists($e, 'getBindings') ? $e->getBindings() : null,
            ]);
            return redirect()->back()->with('error', 'Gagal menghapus kantor (SQL): ' . $sqlMessage);
        } catch (Throwable $e) {
            \Log::error('Branch delete error', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return redirect()->back()->with('error', 'Gagal menghapus kantor: ' . $e->getMessage());
        }
    }
}
