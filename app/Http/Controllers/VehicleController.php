<?php

namespace App\Http\Controllers;

use App\Http\Requests\Vehicle\CreateVehicleRequest;
use App\Http\Requests\Vehicle\UpdateVehicleRequest;
use Throwable;
use Inertia\Inertia;
use App\Models\Branch;
use App\Models\Vehicle;
use App\Models\Employee;
use App\Models\Department;
use App\Models\VehicleType;
use Illuminate\Http\Request;
use App\Models\VehicleFilePath;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;

class VehicleController extends Controller
{
    public function indexPrime(Request $request)
    {
        $perPage = max(1, min((int) $request->get('per_page', 10), 100));
        $q = trim((string) $request->get('q', ''));
        $sortField = $request->get('sort_field', 'id');
        $sortOrder = (int) $request->get('sort_order', 1);

        $allowedSorts = ['id', 'name', 'brand', 'asset_code', 'plate_number', 'type', 'status'];
        if (!in_array($sortField, $allowedSorts, true)) {
            $sortField = 'id';
        }
        $direction = $sortOrder === -1 ? 'desc' : 'asc';

        $query = Vehicle::with(['branch:id,name', 'department:id,name']);
        if ($q !== '') {
            $query->where(function ($w) use ($q) {
                $w->where('name', 'like', "%$q%")
                    ->orWhere('brand', 'like', "%$q%")
                    ->orWhere('asset_code', 'like', "%$q%")
                    ->orWhere('plate_number', 'like', "%$q%")
                    ->orWhere('type', 'like', "%$q%")
                    ->orWhere('status', 'like', "%$q%")
                    ->orWhere('description', 'like', "%$q%")
                ;
            });
        }

        $vehicles = $query->orderBy($sortField, $direction)
            ->paginate($perPage)
            ->withQueryString()
            ->onEachSide(1)
            ->through(function ($v) {
                return [
                    'id' => $v->id,
                    'name' => $v->name,
                    'brand' => $v->brand,
                    'asset_code' => $v->asset_code,
                    'plate_number' => $v->plate_number,
                    'type' => $v->type,
                    'status' => $v->status,
                    'branch_id' => $v->branch_id,
                    'department_id' => $v->department_id,
                    'branch' => $v->branch?->name,
                    'department' => $v->department?->name,
                    'description' => $v->description,
                ];
            });

        $branches = Branch::orderBy('name')->get(['id', 'name']);
        $departments = Department::orderBy('name')->get(['id', 'name']);

        return Inertia::render('Admin/Vehicles/Index', [
            'vehicles' => $vehicles,
            'branches' => $branches,
            'departments' => $departments,
            'filters' => [
                'q' => $q,
                'sort_field' => $sortField,
                'sort_order' => $sortOrder,
                'per_page' => $perPage,
            ],
        ]);
    }

    public function index(Request $request)
    {

        $sortBy = $request->get('sortBy', 'id');
        $sortDirection = $request->get('sortDirection', 'desc');

        $search = $request->input('search');
        $perPage = $request->input('per_page', 10);

        // Check user role and branch
        $user = Auth::user();
        $isSuperadmin = $user->hasRole('Superadmin');
        $userBranchId = $user->employee->branch_id ?? null;

        // New: filters and grouping inputs
        $groupBy = $request->input('group_by'); // expected: branch|employee|status|type
        $branchId = $request->input('branch_id');

        // If user is not Superadmin and not branch_id 2, force filter by their branch_id
        if (!$isSuperadmin && $userBranchId && $userBranchId != 2) {
            $branchId = $userBranchId;
        }

        $employeeId = $request->input('employee_id');
        $status = $request->input('status');
        $vehicleTypeId = $request->input('vehicle_type_id');

        $licenseCode = $request->input('license_code');
        $track = $request->input('track');

        $vehicles = Vehicle::with(['type', 'branch', 'driver', 'files'])
            ->when($search, function ($query, $search) {
                $query->where(function ($query) use ($search) {
                    $query->where('track', 'like', "%{$search}%")
                        ->orWhere('license_code', 'like', "%{$search}%")
                        ->orWhere('chassis_code', 'like', "%{$search}%")
                        ->orWhere('machine_code', 'like', "%{$search}%")
                        ->orWhereHas('type', function ($q) use ($search) {
                            $q->where('name', 'like', "%{$search}%")
                                ->orWhere('category', 'like', "%{$search}%");
                        })
                        ->orWhereHas('branch', function ($q) use ($search) {
                            $q->where('name', 'like', "%{$search}%");
                        })
                        ->orWhereHas('driver', function ($q) use ($search) {
                            $q->where('name', 'like', "%{$search}%");
                        });
                });
            })
            // New: apply filters when provided
            ->when($branchId !== null && $branchId !== '', function ($q) use ($branchId) {
                $q->where('branch_id', $branchId);
            })
            ->when($licenseCode !== null && $licenseCode !== '', function ($q) use ($licenseCode) {
                $q->where('license_code', 'like', "%{$licenseCode}%");
            })
            ->when($track !== null && $track !== '', function ($q) use ($track) {
                $q->where('track', 'like', "%{$track}%");
            })
            ->when($employeeId !== null && $employeeId !== '', function ($q) use ($employeeId) {
                $q->where('employee_id', $employeeId);
            })
            ->when($status !== null && $status !== '', function ($q) use ($status) {
                // Normalize status into 0/1 if it comes as string
                $normalized = is_numeric($status) ? (int) $status : (in_array(strtolower((string) $status), ['1', 'true', 'active'], true) ? 1 : 0);
                $q->where('status', $normalized);
            })
            ->when($vehicleTypeId !== null && $vehicleTypeId !== '', function ($q) use ($vehicleTypeId) {
                $q->where('vehicle_type_id', $vehicleTypeId);
            })
            // Grouping behavior: use orderBy mappings to group visually in the list
            ->when($groupBy, function ($q) use ($groupBy) {
                switch ($groupBy) {
                    case 'branch':
                        $q->orderBy('branch_id')->orderBy('id');
                        break;
                    case 'employee':
                        $q->orderBy('employee_id')->orderBy('id');
                        break;
                    case 'status':
                        $q->orderBy('status')->orderBy('id');
                        break;
                    case 'type':
                        $q->orderBy('vehicle_type_id')->orderBy('id');
                        break;
                    default:
                        // fall back to explicit sort below
                        break;
                }
            })
            // Keep explicit sort if no grouping is requested
            ->when(!$groupBy, function ($q) use ($sortBy, $sortDirection) {
                $q->orderBy($sortBy, $sortDirection);
            })
            ->paginate($perPage)
            ->withQueryString();

        $vehicles->each(function ($item) {
            // Set the first photo as the main photo for display
            $item->photo = optional($item->files->last())->file_path;
            // Store all images for editing
            $item->images = $item->files->map(function ($file) {
                return [
                    'id' => $file->id,
                    'file_path' => $file->file_path,
                ];
            });
        });

        $vehicleTypes = VehicleType::all();
        $branches = $this->getFilteredBranches($isSuperadmin, $userBranchId);
        $employees = $this->getFilteredEmployees($isSuperadmin, $userBranchId);

        // Pass back current group and filters so UI can reflect state
        return Inertia::render('Admin/Vehicles/Index', [
            'vehicles' => $vehicles,
            'vehicleTypes' => $vehicleTypes,
            'branches' => $branches,
            'employees' => $employees,
            'sortBy' => $sortBy,
            'sortDirection' => $sortDirection,
            'search' => $search,
            'groupBy' => $groupBy,
            'filters' => [
                'branch_id' => $branchId,
                'employee_id' => $employeeId,
                'status' => $status,
                'vehicle_type_id' => $vehicleTypeId,
                'license_code' => $licenseCode,
                'track' => $track,
                'per_page' => $perPage,
            ],
        ]);
    }

    public function store(CreateVehicleRequest $request)
    {
        $data = $request->validated();

        $file_paths = [];

        try {
            DB::transaction(function () use ($data, $request, &$file_paths) {
                // Handle vehicle_type_id - if it's a string, create a new VehicleType
                $vehicleTypeId = $data['vehicle_type_id'];
                $vehicleType = VehicleType::find($vehicleTypeId);
                if (!$vehicleType) {
                    // Parse the string to extract name and category
                    // Format: "Name - Category"
                    $parts = explode(' - ', $vehicleTypeId, 2);
                    $name = trim($parts[0]);
                    $category = isset($parts[1]) ? trim($parts[1]) : 'General';

                    // Check if it already exists
                    $vehicleType = VehicleType::firstOrCreate(
                        ['name' => $name, 'category' => $category]
                    );
                    $vehicleTypeId = $vehicleType->id;
                }

                $vehicle = Vehicle::create([
                    'vehicle_type_id' => $vehicleTypeId,
                    'branch_id'       => $data['branch_id'] ?? null,
                    'employee_id'     => $data['employee_id'] ?? null,
                    'track'           => $data['track'] ?? null,
                    'license_code'    => $data['license_code'] ?? null,
                    'chassis_code'    => $data['chassis_code'] ?? null,
                    'machine_code'    => $data['machine_code'] ?? null,
                    'status'          => $data['status'],
                ]);

                if ($request->hasFile('files')) {
                    foreach ($request->file('files') as $file) {
                        $file_path = $file->store('vehicles', 'public');
                        $file_paths[] = $file_path;

                        VehicleFilePath::create([
                            'vehicle_id' => $vehicle->id,
                            'file_path'  => $file_path,
                        ]);
                    }
                }
            });

            return redirect()->back()->with('success', 'Kendaraan berhasil ditambahkan.');
        } catch (Throwable $e) {
            Log::error('Gagal menambah kendaraan', ['err' => $e->getMessage()]);

            foreach ($file_paths as $path) {
                if (Storage::disk('public')->exists($path)) {
                    Storage::disk('public')->delete($path);
                }
            }

            return redirect()->back()->with('error', 'Gagal menambah kendaraan.');
        }
    }

    public function show(Vehicle $vehicle)
    {
        $vehicle->load('type', 'branch', 'driver', 'documents.files', 'services', 'inspections.checklist.questions.answers', 'inspections.submittedBy', 'files', 'latestOdometer');

        $vehicleDocuments = $vehicle->documents;

        $vehicleServices = $vehicle->services->sortByDesc('date')->values();
        $vehicle->service = $vehicle->services
            ->sortByDesc('id')
            ->first();

        $lastInspection = $vehicle->inspections
            ->sortByDesc('id')
            ->first();

        if ($lastInspection && $lastInspection->checklist) {
            $totalQuestions = $lastInspection->checklist->questions->count();
            $answeredQuestions = $lastInspection->checklist->questions
                ->filter(fn($q) => $q->answers->isNotEmpty())
                ->count();

            $vehicle->progress = $totalQuestions > 0
                ? round(($answeredQuestions / $totalQuestions) * 100, 2)
                : 0;
        } else {
            $vehicle->progress = 0;
        }

        $vehicle->inspection = $lastInspection;

        $vehicleInspections = $vehicle->inspections;
        $vehicleInspections->each(function ($item) {
            if ($item->checklist) {
                $totalQuestions = $item->checklist->questions->count();
                $answeredQuestions = $item->checklist->questions->filter(function ($q) {
                    return $q->answers->isNotEmpty();
                })->count();

                $item->progress = $totalQuestions > 0
                    ? round(($answeredQuestions / $totalQuestions) * 100, 2)
                    : 0;
            } else {
                $item->progress = 0;
            }

            // Add inspector name for safe frontend access
            $item->inspector_name = optional($item->submittedBy)->name;
        });

        $images = VehicleFilePath::where('vehicle_id', $vehicle->id)->get();

        $vehicleOdometerHistory = \App\Models\VehicleHistoryOdometer::where('vehicle_id', $vehicle->id)
            ->orderByDesc('tanggal')
            ->orderByDesc('id')
            ->get()
            ->map(function ($item) {
                return [
                    'id' => $item->id,
                    'tanggal' => $item->tanggal ? $item->tanggal->format('Y-m-d') : null,
                    'last_km' => (int) $item->last_km,
                    'current_km' => (int) $item->current_km,
                    'created_at' => optional($item->created_at)?->format('Y-m-d H:i:s'),
                ];
            });

        return Inertia::render('Admin/Vehicles/Show', compact('vehicle', 'vehicleDocuments', 'vehicleServices', 'vehicleInspections', 'images', 'vehicleOdometerHistory'));
    }

    public function update(UpdateVehicleRequest $request, Vehicle $vehicle)
    {
        Log::info('Update vehicle request', ['request' => $request->all()]);
        $data = $request->validated();

        $file_paths = [];

        try {
            DB::transaction(function () use ($vehicle, $data, $request, &$file_paths) {
                // Handle vehicle_type_id - if it's a string, create a new VehicleType

                $vehicleTypeId = null;
                if (isset($data['vehicle_type_id'])) {
                    $vehicleTypeId = $data['vehicle_type_id'];

                    $vehicleType = VehicleType::find($vehicleTypeId);
                    if (!$vehicleType) {
                        // Parse the string to extract name and category
                        // Format: "Name - Category"
                        $parts = explode(' - ', $vehicleTypeId, 2);
                        $name = trim($parts[0]);
                        $category = isset($parts[1]) ? trim($parts[1]) : 'General';

                        // Check if it already exists
                        $vehicleType = VehicleType::firstOrCreate(
                            ['name' => $name, 'category' => $category]
                        );
                        $vehicleTypeId = $vehicleType->id;
                    }
                }

                // simpan vehicle dulu
                $vehicle->update(array_filter([
                    'vehicle_type_id' => $vehicleTypeId,
                    'branch_id'       => $data['branch_id'] ?? null,
                    'employee_id'     => $data['employee_id'] ?? null,
                    'track'           => $data['track'] ?? null,
                    'license_code'    => $data['license_code'] ?? null,
                    'chassis_code'    => $data['chassis_code'] ?? null,
                    'machine_code'    => $data['machine_code'] ?? null,
                ]) + ['status' => $data['status']]);

                // Handle deleted images
                if ($request->has('deleted_images') && is_array($request->deleted_images) && count($request->deleted_images) > 0) {
                    $filesToDelete = $vehicle->files()->whereIn('id', $request->deleted_images)->get();
                    foreach ($filesToDelete as $file) {
                        Storage::disk('public')->delete($file->file_path);
                        $file->delete();
                    }
                }

                // Handle new file uploads
                if ($request->hasFile('files')) {
                    foreach ($request->file('files') as $file) {
                        $file_path = $file->store('vehicles', 'public');
                        $file_paths[] = $file_path;

                        VehicleFilePath::create([
                            'vehicle_id' => $vehicle->id,
                            'file_path'  => $file_path,
                        ]);
                    }
                }
            });

            return redirect()->back()->with('success', 'Kendaraan berhasil diubah');
        } catch (Throwable $e) {
            Log::error('Gagal mengubah kendaraan', ['err' => $e->getMessage()]);

            foreach ($file_paths as $path) {
                if (Storage::disk('public')->exists($path)) {
                    Storage::disk('public')->delete($path);
                }
            }

            return redirect()->back()->with('error', 'Gagal mengubah kendaraan');
        }
    }

    public function destroy(Vehicle $vehicle)
    {
        try {
            // Check for related data before deletion
            $relatedData = [];

            // Check services
            if ($vehicle->services()->count() > 0) {
                $relatedData[] = 'riwayat servis';
            }

            // Check odometer history
            if ($vehicle->odometers()->count() > 0) {
                $relatedData[] = 'riwayat odometer';
            }

            // Check inspections
            if ($vehicle->inspections()->count() > 0) {
                $relatedData[] = 'inspeksi';
            }

            // If there are related data, prevent deletion
            if (!empty($relatedData)) {
                $relatedDataString = implode(', ', $relatedData);
                return redirect()->back()->with(
                    'error',
                    "Kendaraan tidak dapat dihapus karena masih memiliki data terkait: {$relatedDataString}. " .
                        "Silakan hapus data terkait terlebih dahulu."
                );
            }

            DB::transaction(function () use ($vehicle) {
                $vehicle->delete();
            });

            return redirect()->back()->with('success', 'Kendaraan berhasil dihapus');
        } catch (\Throwable $e) {
            Log::error('Vehicle destroy error', ['err' => $e->getMessage()]);
            return redirect()->back()->with('error', 'Gagal menghapus kendaraan');
        }
    }

    /**
     * Get filtered branches based on user role
     */
    private function getFilteredBranches(bool $isSuperadmin, ?int $branchId)
    {
        $query = Branch::orderBy('name');

        if (!$isSuperadmin && $branchId && $branchId != 2) {
            $query->where('id', $branchId);
        }

        return $query->get();
    }

    /**
     * Get filtered employees based on user role
     */
    private function getFilteredEmployees(bool $isSuperadmin, ?int $branchId)
    {
        $query = Employee::orderBy('name');

        if (!$isSuperadmin && $branchId && $branchId != 2) {
            $query->where('branch_id', $branchId);
        }

        return $query->get();
    }
}
