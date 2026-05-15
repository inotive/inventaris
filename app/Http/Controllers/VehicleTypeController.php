<?php

namespace App\Http\Controllers;

use Exception;
use Inertia\Inertia;
use App\Models\VehicleType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Gate;

class VehicleTypeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        abort_unless(Gate::allows('vehicle_categories.view'), 403, 'Anda tidak memiliki akses untuk melihat data kategori kendaraan');

        $sortBy = $request->get('sortBy', 'id');
        $sortDirection = $request->get('sortDirection', 'asc');

        $search = $request->input('search');
        $perPage = $request->input('per_page', 10);

        $vehicleTypes = VehicleType::withCount(['vehicles as total'])
            ->when($search, function ($query, $search) {
                $query->where('name', 'like', "%{$search}%")
                    ->orWhere('category', 'like', "%{$search}%");
            })
            ->orderBy($sortBy, $sortDirection)
            ->paginate($perPage)
            ->withQueryString();

        return Inertia::render('Admin/VehicleTypes/Index', compact('vehicleTypes', 'sortBy', 'sortDirection', 'search'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        abort_unless(Gate::allows('vehicle_categories.create'), 403, 'Anda tidak memiliki akses untuk menambah kategori kendaraan');
        
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        abort_unless(Gate::allows('vehicle_categories.create'), 403, 'Anda tidak memiliki akses untuk menambah kategori kendaraan');

        $data = $request->validate([
            'name' => 'required|string|max:255|unique:vehicle_types,name',
            'category' => 'required|string|max:255',
        ]);

        VehicleType::create($data);

        return redirect()->back()->with('success', 'Tipe kendaraan berhasil ditambahkan.');
    }

    /**
     * Display the specified resource.
     */
    public function show(VehicleType $vehicleType)
    {
        abort_unless(Gate::allows('vehicle_categories.view'), 403, 'Anda tidak memiliki akses untuk melihat detail kategori kendaraan');
        
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(VehicleType $vehicleType)
    {
        abort_unless(Gate::allows('vehicle_categories.edit'), 403, 'Anda tidak memiliki akses untuk mengubah kategori kendaraan');
        
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, VehicleType $vehicleType)
    {
        abort_unless(Gate::allows('vehicle_categories.edit'), 403, 'Anda tidak memiliki akses untuk mengubah kategori kendaraan');

        $data = $request->validate([
            'name' => 'required|string|max:255|unique:vehicle_types,name,' . $vehicleType->id,
            'category' => 'required|string|max:255',
        ]);

        $vehicleType->update($data);

        return back()->with('success', 'Tipe kendaraan berhasil diperbarui.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(VehicleType $vehicleType)
    {
        abort_unless(Gate::allows('vehicle_categories.delete'), 403, 'Anda tidak memiliki akses untuk menghapus kategori kendaraan');

        DB::beginTransaction();

        try {
            if ($vehicleType->vehicles()->exists()) {
                return redirect()->back()->with('error', 'Tipe memiliki kendaraan aktif.');
            }

            $vehicleType->delete();

            DB::commit();

            return redirect()->back()->with('success', 'Tipe kendaraan berhasil dihapus.');
        } catch (Exception $e) {
            Log::error($e->getMessage());

            DB::rollBack();

            return redirect()->back()->with('error', 'Terjadi kesalahan saat menghapus tipe kendaraan: ' . $e->getMessage());
        }
    }
}