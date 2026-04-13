<?php

namespace App\Http\Controllers\Api\v1;

use App\Helpers\ResponseFormatter;
use App\Http\Controllers\Controller;
use App\Http\Requests\VehicleOdometerHistory\UpdateVehicleOdometerHistory;
use App\Models\Vehicle;
use App\Models\VehicleHistoryOdometer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class VehicleOdometerHistoryController extends Controller
{
    /**
     * Body params (JSON):
     * - vehicle_id: required|integer
     * - month: optional|string format YYYY-MM
     * - date_from, date_to: optional|string Y-m-d (used when month not provided)
     * - limit: optional|int default 20
     */
    public function index(Request $request)
    {
        $vehicleId = $request->input('vehicle_id');

        $query = VehicleHistoryOdometer::query()
            ->with('vehicle.type')
            ->when($vehicleId, function ($query) use ($vehicleId) {
                $query->where('vehicle_id', $vehicleId);
            })
            ->orderByDesc('tanggal')
            ->orderByDesc('id');
        $p = $query->get();

        $items = collect($p)->map(function (VehicleHistoryOdometer $row) {
            return [
                'id' => $row->id,
                'vehicle' => $row->vehicle,
                'vehicle_id' => $row->vehicle_id,

                'tanggal' => optional($row->tanggal)?->format('Y-m-d'),
                'last_km' => (int) $row->last_km,
                'current_km' => (int) $row->current_km,
                'created_by' => $row->created_by,
                'created_at' => optional($row->created_at)?->toDateTimeString(),
                'updated_at' => optional($row->updated_at)?->toDateTimeString(),
            ];
        });

        return ResponseFormatter::success(
            $items,
            'histories',
            'Vehicle odometer history'
        );
    }

    public function update(UpdateVehicleOdometerHistory $request)
    {
        $data = $request->validated();
        $vehicle = Vehicle::findOrFail($data['vehicle_id']);

        $lastOdometer = $vehicle->odometers()->latest()->first();

        $data['last_km'] = $lastOdometer ? $lastOdometer->current_km : 0;
        $data['created_by'] = Auth::id();
        $data['tanggal'] = $data['tanggal'] ?? now()->format('Y-m-d');
        $data['inspection_id'] = $data['inspection_id'] ?? null;

        $vehicle->odometers()->create($data);

        return ResponseFormatter::success(null, 'Vehicle odometer history added successfully');
    }
}
