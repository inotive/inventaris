<?php

namespace App\Http\Controllers;

use App\Models\Item;
use Inertia\Inertia;
use App\Models\Branch;
use App\Models\Vehicle;
use App\Models\Checklist;
use App\Models\Inspection;
use App\Models\ItemStock;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class VehicleInspectionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $user = Auth::user()->load('employee.branch');

        // Check user role and branch
        $isSuperadmin = $user->hasRole('Superadmin');
        $userBranchId = $user->employee->branch_id ?? null;

        $perPage = $request->input('per_page', 10);

        $branchId = $request->integer('branch_id') ?: null;

        // If user is not Superadmin and not branch_id 2, force filter by their branch_id
        if (!$isSuperadmin && $userBranchId && $userBranchId != 2) {
            $branchId = $userBranchId;
        }

        $filters = [
            'q' => $request->string('q')->toString(),
            'branch_id' => $branchId,
            'vehicle' => $request->string('vehicle')->toString(),
            'inspection_number' => $request->string('inspection_number')->toString(),
            'checklist' => $request->string('checklist')->toString(),
            'status' => $request->string('status')->toString(),
            'location' => $request->string('location')->toString(),
            'submitted_by' => $request->string('submitted_by')->toString(),
            'date_from' => $request->string('date_from')->toString(),
            'date_to' => $request->string('date_to')->toString(),
        ];

        $sortBy = $request->get('sortBy', 'id');
        $sortDirection = $request->get('sortDirection', 'desc');
        $groupBy = $request->get('groupBy');

        $query = Inspection::query()
            ->excludeDuplicates()
            ->with([
                'checklist.category',
                'model.files', // Load polymorphic relation (Vehicle) with files
                'submittedBy.employee',
                'location',
                'answers',
            ])
            ->where(function ($q) {
                // Inspeksi yang model_type-nya Vehicle (data lengkap)
                $q->where(function ($sub) {
                    $sub->where('model_type', Vehicle::class)
                        ->whereNotNull('model_id');
                })
                // ATAU inspeksi yang model_type kosong tapi checklist-nya kategori Kendaraan
                ->orWhere(function ($sub) {
                    $sub->where(function ($inner) {
                        $inner->whereNull('model_type')
                              ->orWhere('model_type', '');
                    })
                    ->whereHas('checklist.category', function ($cq) {
                        $cq->where('name', 'Kendaraan');
                    });
                });
            });

        // Global search (q)
        if ($filters['q']) {
            $search = $filters['q'];
            $query->where(function ($q) use ($search) {
                $q->where('inspection_number', 'like', "%{$search}%")
                    ->orWhereHas('checklist', function ($cq) use ($search) {
                        $cq->where('name', 'like', "%{$search}%");
                    });
            });
        }

        // Filter by vehicle (license_code) - dari polymorphic model
        if ($filters['vehicle']) {
            $query->where(function ($q) use ($filters) {
                $q->whereHasMorph('model', [Vehicle::class], function ($vq) use ($filters) {
                    $vq->where('license_code', 'like', "%{$filters['vehicle']}%");
                });
            });
        }

        // Filter by inspection number
        if ($filters['inspection_number']) {
            $query->where('inspection_number', 'like', "%{$filters['inspection_number']}%");
        }

        // Filter by checklist name
        if ($filters['checklist']) {
            $query->whereHas('checklist', function ($cq) use ($filters) {
                $cq->where('name', 'like', "%{$filters['checklist']}%");
            });
        }

        // Filter by status
        if ($filters['status']) {
            $query->where('status', $filters['status']);
        }

        // Filter by location
        if ($filters['location']) {
            $query->whereHas('location', function ($lq) use ($filters) {
                $lq->where('name', 'like', "%{$filters['location']}%");
            });
        }

        // Filter by submitted_by (creator)
        if ($filters['submitted_by']) {
            $query->whereHas('submittedBy', function ($sq) use ($filters) {
                $sq->where('name', 'like', "%{$filters['submitted_by']}%");
            });
        }

        // Use submit_date as the business date for filtering
        if ($filters['date_from']) $query->whereDate('submit_date', '>=', $filters['date_from']);
        if ($filters['date_to']) $query->whereDate('submit_date', '<=', $filters['date_to']);

        // Filter branch via polymorphic model (Vehicle)
        // Always apply branch filter if user is not Superadmin and not branch_id 2
        if (!$isSuperadmin && $userBranchId && $userBranchId != 2) {
            $query->whereHasMorph('model', [Vehicle::class], function ($vq) use ($userBranchId) {
                $vq->where('branch_id', $userBranchId);
            });
        } elseif ($filters['branch_id']) {
            $bid = (int) $filters['branch_id'];
            $query->whereHasMorph('model', [Vehicle::class], function ($vq) use ($bid) {
                $vq->where('branch_id', $bid);
            });
        }

        $sortDirection = strtolower($sortDirection) === 'asc' ? 'asc' : 'desc';

        $validSortColumns = ['inspection_number', 'submit_date', 'checklist.name', 'checklist.vehicle.license_code'];

        // Map UI sortBy 'created_at' to actual column 'submit_date'
        $orderColumn = $sortBy === 'created_at' ? 'submit_date' : $sortBy;
        if (in_array($orderColumn, $validSortColumns)) {
            $query->orderBy($orderColumn, $sortDirection);
        } else {
            $query->orderBy('id', $sortDirection);
        }

        $inspections = $query->paginate($perPage)->withQueryString();

        return Inertia::render('Admin/VehicleInspections/Index', [
            'inspections' => $inspections->through(function ($gt) {
                // Get vehicle: prioritas dari polymorphic relation
                $vehicle = $gt->model_type === Vehicle::class ? $gt->model : null;

                // Get vehicle photo
                $photo = null;
                if ($vehicle) {
                    $latestFile = $vehicle->files()->latest()->first();
                    $photo = $latestFile ? $latestFile->file_path : null;
                }

                // Calculate condition percentage
                $totalQuestions = $gt->answers->count();

                // For vehicle inspection, count "ya" answers as good condition
                // If is_correct field exists, use it; otherwise use answer value
                $goodAnswers = $gt->answers->filter(function ($answer) {
                    // Check if is_correct field is set
                    if (isset($answer->is_correct)) {
                        return $answer->is_correct === true || $answer->is_correct === 1;
                    }
                    // Otherwise, count "ya" answers as good
                    return strtolower(trim($answer->answer ?? '')) === 'ya';
                })->count();

                $conditionPercentage = $totalQuestions > 0 ? round(($goodAnswers / $totalQuestions) * 100) : 0;

                // Get odometer data from vehicle_history_odometers
                // Per requirement, each inspection row should show the kilometer update
                // from the inspection itself (linked via inspection_id)
                $odometerData = null;
                if ($vehicle) {
                    $odometerRecord = \DB::table('vehicle_history_odometers')
                        ->where('vehicle_id', $vehicle->id)
                        ->where('inspection_id', $gt->id)
                        ->orderBy('tanggal', 'desc')
                        ->orderBy('created_at', 'desc')
                        ->first();

                    if ($odometerRecord) {
                        $odometerData = [
                            'current_km' => $odometerRecord->current_km,
                            'last_km' => $odometerRecord->last_km,
                        ];
                    }
                }

                return [
                    'id' => $gt->id,
                    'inspection_number' => $gt->inspection_number,
                    'vehicle' => optional($vehicle)->license_code,
                    'vehicle_id' => optional($vehicle)->id,
                    'photo' => $photo,
                    'license_plate' => optional($vehicle)->license_plate,
                    'route' => optional($vehicle)->route,
                    'checklist' => [
                        'name' => optional($gt->checklist)->name,
                        'category' => optional($gt->checklist->category)->name,
                        'type' => optional($gt->checklist)->type,
                    ],
                    'checklist_code' => optional($gt->checklist)->sop_code,
                    'submit_date' => $gt->submit_date ? $gt->submit_date->locale('id')->translatedFormat('d F Y H:i') : '-',
                    'status' => $gt->status,
                    'location' => optional($gt->location)->name,
                    'condition_percentage' => $conditionPercentage,
                    'odometer_data' => $odometerData,
                    'submitted_by' => optional($gt->submittedBy)->name,
                    'approved_by' => optional($gt->approvedBy)->name,
                    'created_at' => $gt->submit_date ? $gt->submit_date->locale('id')->translatedFormat('d F Y') : '-',
                ];
            }),
            'branches' => $this->getFilteredBranches($isSuperadmin, $userBranchId),
            'filters' => array_merge($filters),
            'sortBy' => $sortBy,
            'sortDirection' => $sortDirection,
            'groupBy' => in_array($groupBy, ['ordered_by'], true) ? $groupBy : null,
            'sidebarCounts' => [
                'total' => Inspection::excludeDuplicates()
                    ->where(function ($q) {
                        $q->where(function ($sub) {
                            $sub->where('model_type', Vehicle::class)
                                ->whereNotNull('model_id');
                        })->orWhere(function ($sub) {
                            $sub->where(function ($inner) {
                                $inner->whereNull('model_type')
                                      ->orWhere('model_type', '');
                            })->whereHas('checklist.category', function ($cq) {
                                $cq->where('name', 'Kendaraan');
                            });
                        });
                    })->count(),
            ],
        ]);
    }

    public function index0(Request $request)
    {
        $sortBy = $request->get('sortBy', 'id');
        $sortDirection = $request->get('sortDirection', 'asc');

        $search = $request->input('search');
        $perPage = $request->input('per_page', 10);

        // Build inspection listing with required columns
        $checklists = Inspection::query()
            ->with([
                'checklist:id,name',
                'submittedBy:id,name',
                'model'
            ])
            ->where('model_type', Vehicle::class)
            ->whereNotNull('model_id')
            ->when($search, function ($q) use ($search) {
                $q->where(function ($w) use ($search) {
                    $w->where('inspection_number', 'like', "%{$search}%")
                        ->orWhereHas('checklist', function ($cq) use ($search) {
                            $cq->where('name', 'like', "%{$search}%");
                        })
                        ->orWhereHas('submittedBy', function ($sq) use ($search) {
                            $sq->where('name', 'like', "%{$search}%");
                        });
                });
            })
            ->orderBy($sortBy, $sortDirection)
            ->paginate($perPage)
            ->withQueryString();
        dd($checklists);

        $checklists->getCollection()->transform(function ($r) {
            $vehicleName = null;
            $driverName = null;
            if ($r->model && $r->model_type === Vehicle::class) {
                $vehicleName = $r->model->name ?? null;
                $driverName = optional($r->model->driver)->name;
            }
            return [
                'id' => $r->id,
                'reference' => $r->inspection_number ?: ('INS-' . str_pad($r->id, 6, '0', STR_PAD_LEFT)),
                'vehicle' => $vehicleName,
                'driver' => $driverName,
                'checklist' => optional($r->checklist)->name,
                'submit_date' => optional($r->submit_date)->format('Y-m-d H:i:s'),
                'submitted_by' => optional($r->submittedBy)->name,
                'status' => $r->status,
            ];
        });

        return Inertia::render('Admin/VehicleInspections/Index0', [
            'checklists' => $checklists,
            'sortBy' => $sortBy,
            'sortDirection' => $sortDirection,
            'search' => $search,
        ]);
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
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
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
}
