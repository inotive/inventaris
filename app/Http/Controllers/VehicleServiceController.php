<?php

namespace App\Http\Controllers;

use App\Http\Requests\VehicleService\VehicleServiceRequest;
use Throwable;
use Inertia\Inertia;
use App\Models\Branch;
use App\Models\Vehicle;
use App\Models\VehicleService;
use App\Models\VehicleFilePath;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;

class VehicleServiceController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        // Default sort by latest service date
        $sortBy = $request->get('sortBy', 'date');
        $sortDirection = $request->get('sortDirection', 'desc');

        $search = $request->input('search');
        $vehicleName = $request->input('vehicle_name');
        $licenseCode = $request->input('license_code');
        $categoryName = $request->input('category_name');
        $subCategoryName = $request->input('sub_category_name');
        $perPage = $request->input('per_page', 10);
        $dateFrom = $request->input('date_from');
        $dateTo = $request->input('date_to');

        // Check user role and branch
        $user = Auth::user();
        $isSuperadmin = $user->hasRole('Superadmin');
        $userBranchId = $user->employee->branch_id ?? null;

        $vehicleServices = VehicleService::with(['vehicle', 'vehicle.type', 'vehicle.files', 'vehicle.branch'])
            ->when($search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('note', 'like', "%{$search}%")
                        ->orWhere('date', 'like', "%{$search}%")
                        ->orWhere('cost', 'like', "%{$search}%")
                        ->orWhereHas('vehicle', function ($q2) use ($search) {
                            $q2->where('license_code', 'like', "%{$search}%")
                                ->orWhereHas('type', function ($q3) use ($search) {
                                    $q3->where('name', 'like', "%{$search}%");
                                });
                        });
                });
            })
            ->when($vehicleName, function ($query, $vehicleName) {
                $query->whereHas('vehicle.type', function ($q) use ($vehicleName) {
                    $q->where(function ($sub) use ($vehicleName) {
                        $sub->where('name', 'like', "%{$vehicleName}%")
                            ->orWhere('category', 'like', "%{$vehicleName}%");
                    });
                });
            })
            ->when($licenseCode, function ($query, $licenseCode) {
                $query->whereHas('vehicle', function ($q) use ($licenseCode) {
                    $q->where('license_code', 'like', "%{$licenseCode}%");
                });
            })
            ->when($dateFrom, function ($query, $dateFrom) {
                $query->whereDate('date', '>=', $dateFrom);
            })
            ->when($dateTo, function ($query, $dateTo) {
                $query->whereDate('date', '<=', $dateTo);
            })
            ->when($categoryName, function ($query, $categoryNames) {
                if (is_array($categoryNames) && count($categoryNames) > 0) {
                    $query->where(function ($q) use ($categoryNames) {
                        foreach ($categoryNames as $name) {
                            $q->orWhereJsonContains('category_name', $name);
                        }
                    });
                } else if (is_string($categoryNames)) {
                    $query->whereJsonContains('category_name', $categoryNames);
                }
            })
            ->when($subCategoryName, function ($query, $subCategoryNames) {
                if (is_array($subCategoryNames) && count($subCategoryNames) > 0) {
                    $query->where(function ($q) use ($subCategoryNames) {
                        foreach ($subCategoryNames as $name) {
                            $q->orWhereJsonContains('sub_category_name', $name);
                        }
                    });
                } else if (is_string($subCategoryNames)) {
                    $query->whereJsonContains('sub_category_name', $subCategoryNames);
                }
            })
            // Filter by branch_id if user is not Superadmin and not Branch 2
            ->when(!$isSuperadmin && $userBranchId != 2 && $userBranchId, function ($query) use ($userBranchId) {
                $query->whereHas('vehicle', function ($q) use ($userBranchId) {
                    $q->where('branch_id', $userBranchId);
                });
            })
            ->orderBy($sortBy, $sortDirection)
            ->paginate($perPage)
            ->withQueryString();


        $vehicleServices->each(function ($item) {
            $item->name = optional($item->vehicle->type)->name;
            $item->category = optional($item->vehicle->type)->category;
            $item->photo = optional($item->vehicle->files->where('type', 'photo')->last())->file_path;

            // Load attachments for this service
            $item->attachments = VehicleFilePath::where('service_id', $item->id)
                ->get()
                ->map(function ($file) {
                    return [
                        'id' => $file->id,
                        'file_path' => $file->file_path,
                        'url' => Storage::url($file->file_path),
                    ];
                });
        });

        $vehicles = $this->getFilteredVehicles($isSuperadmin, $userBranchId);
        $branches = $this->getFilteredBranches($isSuperadmin, $userBranchId);

        // Get distinct categories and sub-categories for autocomplete (flattened from JSON arrays)
        if (Schema::hasColumn('vehicle_services', 'category_name')) {
            $categoriesRaw = VehicleService::whereNotNull('category_name')->get()->pluck('category_name')->flatten()->unique()->values()->all();
            $categories = collect($categoriesRaw)->map(function ($name) {
                return ['id' => $name, 'name' => $name];
            })->values()->all();
        } else {
            $categories = [];
        }

        if (Schema::hasColumn('vehicle_services', 'sub_category_name')) {
            $subCategoriesRaw = VehicleService::whereNotNull('sub_category_name')->get()->pluck('sub_category_name')->flatten()->unique()->values()->all();
            $subCategories = collect($subCategoriesRaw)->map(function ($name) {
                return ['id' => $name, 'name' => $name];
            })->values()->all();
        } else {
            $subCategories = [];
        }

        return Inertia::render('Admin/VehicleServices/Index', compact('vehicleServices', 'vehicles', 'branches', 'sortBy', 'sortDirection', 'search', 'categories', 'subCategories'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(VehicleServiceRequest $request)
    {
        $data = $request->validated();

        $file_paths = [];

        try {
            DB::transaction(function () use ($data, $request, &$file_paths) {
                $vehicleService = VehicleService::create([
                    'vehicle_id' => $data['vehicle_id'],

                    'category_name' => $data['category_name'],
                    'sub_category_name' => $data['sub_category_name'],
                    'cost'       => $data['cost'] ?? 0,
                    'distance'   => $data['distance'],
                    'date'       => $data['date'],
                    'note'       => $data['note'] ?? null,
                ]);

                if ($request->hasFile('files')) {
                    foreach ($request->file('files') as $file) {
                        $file_path = $file->store('vehicle_services', 'public');
                        $file_paths[] = $file_path;


                        VehicleFilePath::create([
                            'service_id' => $vehicleService->id,
                            'vehicle_id' => $data['vehicle_id'],
                            'file_path'  => $file_path,
                        ]);
                    }
                }
            });

            return redirect()->back()->with('success', 'Data service kendaraan berhasil ditambahkan.');
        } catch (Throwable $e) {
            Log::error('Gagal menambah data service kendaraan', ['err' => $e->getMessage()]);

            foreach ($file_paths as $path) {
                if (Storage::disk('public')->exists($path)) {
                    Storage::disk('public')->delete($path);
                }
            }

            return redirect()->back()->with('error', 'Gagal menambah data service kendaraan.');
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(VehicleService $vehicleService)
    {
        // Hak akses: lihat detail
        abort_unless(Gate::allows('vehicle_services.view'), 403, 'Anda tidak memiliki akses untuk melihat detail service kendaraan');

        $vehicleService->load(['vehicle.type', 'vehicle.files']);

        // Load attachments
        $attachments = VehicleFilePath::where('service_id', $vehicleService->id)
            ->get()
            ->map(function ($file) {
                return [
                    'id' => $file->id,
                    'file_path' => $file->file_path,
                    'url' => Storage::url($file->file_path),
                ];
            });

        // Format data untuk frontend
        $vehicleService->name = optional($vehicleService->vehicle->type)->name;
        $vehicleService->category = optional($vehicleService->vehicle->type)->category;
        $vehicleService->photo = optional($vehicleService->vehicle->files->where('type', 'photo')->last())->file_path;
        $vehicleService->attachments = $attachments;

        return Inertia::render('Admin/VehicleServices/Show', [
            'vehicleService' => $vehicleService,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(VehicleService $vehicleService)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, VehicleService $vehicleService)
    {
        // Hak akses: edit
        abort_unless(Gate::allows('vehicle_services.edit'), 403, 'Anda tidak memiliki akses untuk mengubah data service kendaraan');

        $data = $request->validate([
            'vehicle_id'         => ['required', 'exists:vehicles,id'],
            'category_name'      => ['required', 'array'],
            'category_name.*'    => ['string'],
            'sub_category_name'  => ['required', 'array'],
            'sub_category_name.*' => ['string'],
            'cost'               => ['nullable', 'integer'],
            'date'               => ['required', 'date', 'before_or_equal:today'],
            'distance'           => ['nullable', 'integer'],
            'note'               => ['required', 'string', 'max:255'],
            'files'              => ['nullable', 'array'],
            'files.*'            => ['file', 'mimes:jpg,jpeg,png', 'max:5120'],
            'deleted_attachments' => ['nullable', 'array'],
            'deleted_attachments.*' => ['integer'],
        ]);

        // Validasi: minimal harus ada 1 attachment setelah update
        $currentAttachmentsCount = VehicleFilePath::where('service_id', $vehicleService->id)->count();
        $deletedCount = $request->has('deleted_attachments') && is_array($request->deleted_attachments)
            ? count($request->deleted_attachments)
            : 0;
        $newFilesCount = $request->hasFile('files') ? count($request->file('files')) : 0;
        $remainingAfterDelete = $currentAttachmentsCount - $deletedCount;

        // Jika semua attachment dihapus dan tidak ada file baru, validasi gagal
        if ($remainingAfterDelete <= 0 && $newFilesCount === 0) {
            return redirect()->back()
                ->withErrors(['files' => 'Minimal harus ada 1 lampiran. Silakan tambahkan file baru atau batalkan penghapusan gambar yang ada.'])
                ->withInput();
        }

        $file_paths = [];

        try {
            DB::transaction(function () use ($vehicleService, $data, $request, &$file_paths) {
                // Update data service
                $vehicleService->update([
                    'vehicle_id' => $data['vehicle_id'],

                    'category_name' => $data['category_name'],
                    'sub_category_name' => $data['sub_category_name'],
                    'cost'       => $data['cost'] ?? 0,
                    'distance'   => $data['distance'] ?? null,
                    'date'       => $data['date'],
                    'note'       => $data['note'] ?? null,
                ]);

                // Handle deleted attachments
                if ($request->has('deleted_attachments') && is_array($request->deleted_attachments) && count($request->deleted_attachments) > 0) {
                    // Pastikan file yang dihapus benar-benar milik service ini
                    $filesToDelete = VehicleFilePath::where('service_id', $vehicleService->id)
                        ->whereIn('id', $request->deleted_attachments)
                        ->get();

                    foreach ($filesToDelete as $file) {
                        if ($file->file_path && Storage::disk('public')->exists($file->file_path)) {
                            Storage::disk('public')->delete($file->file_path);
                        }
                        $file->delete();
                    }
                }

                // Handle new file uploads
                if ($request->hasFile('files')) {
                    foreach ($request->file('files') as $file) {
                        $file_path = $file->store('vehicle_services', 'public');
                        $file_paths[] = $file_path;

                        VehicleFilePath::create([
                            'service_id' => $vehicleService->id,
                            'vehicle_id' => $data['vehicle_id'],
                            'file_path'  => $file_path,
                        ]);
                    }
                }
            });

            return redirect()->back()->with('success', 'Data service kendaraan berhasil diperbarui.');
        } catch (Throwable $e) {
            Log::error('Gagal memperbarui data service kendaraan', ['err' => $e->getMessage()]);

            // Rollback uploaded files
            foreach ($file_paths as $path) {
                if (Storage::disk('public')->exists($path)) {
                    Storage::disk('public')->delete($path);
                }
            }

            return redirect()->back()->with('error', 'Gagal memperbarui data service kendaraan.');
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(VehicleService $vehicleService)
    {
        try {
            DB::transaction(function () use ($vehicleService) {
                // Delete associated files
                $files = VehicleFilePath::where('service_id', $vehicleService->id)->get();
                foreach ($files as $file) {
                    if ($file->file_path && Storage::disk('public')->exists($file->file_path)) {
                        Storage::disk('public')->delete($file->file_path);
                    }
                    $file->delete();
                }

                // Delete the service record
                $vehicleService->delete();
            });

            return redirect()->back()->with('success', 'Data service kendaraan berhasil dihapus.');
        } catch (\Throwable $e) {
            Log::error('Gagal menghapus data service kendaraan', ['err' => $e->getMessage()]);
            return redirect()->back()->with('error', 'Gagal menghapus data service kendaraan.');
        }
    }

    /**
     * Get filtered vehicles based on user role
     */
    private function getFilteredVehicles(bool $isSuperadmin, ?int $branchId)
    {
        $query = Vehicle::with(['type'])->orderBy('license_code');

        if (!$isSuperadmin && $branchId != 2 && $branchId) {
            $query->where('branch_id', $branchId);
        }

        return $query->get();
    }

    /**
     * Get filtered branches based on user role
     */
    private function getFilteredBranches(bool $isSuperadmin, ?int $branchId)
    {
        $query = Branch::orderBy('name');

        if (!$isSuperadmin && $branchId != 2 && $branchId) {
            $query->where('id', $branchId);
        }

        return $query->get();
    }
}
