<?php

namespace App\Http\Controllers\Api\v1;

use App\Helpers\ResponseFormatter;
use App\Http\Controllers\Controller;
use App\Http\Resources\VehicleResource;
use App\Models\Vehicle;
use Illuminate\Http\Request;

class VehicleController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->get('search');
        $limit = $request->get('limit', 10);

        $vehicles = Vehicle::with(['branch', 'type', 'driver', 'latestOdometer', 'latestService'])
            ->when($search, function ($query) use ($search) {
                $query->where('license_code', 'like', "%{$search}%")
                    ->orWhere('track', 'like', "%{$search}%")
                    ->orWhere('chassis_code', 'like', "%{$search}%")
                    ->orWhere('machine_code', 'like', "%{$search}%");
            })
            ->paginate($limit);

        return ResponseFormatter::successWithPagination(
            VehicleResource::collection($vehicles->items()),
            'vehicles',
            'List of Vehicles',
            $vehicles->total(),
            $vehicles->count(),
            $vehicles->perPage(),
            $vehicles->currentPage(),
            $vehicles->lastPage()
        );
    }

    public function show($id)
    {
        $vehicle = Vehicle::with(['branch', 'department'])->find($id);

        if (!$vehicle) {
            return ResponseFormatter::error('Vehicle not found', 404);
        }

        return ResponseFormatter::success(
            new VehicleResource($vehicle),
            'Vehicle detail retrieved successfully'
        );
    }
}
