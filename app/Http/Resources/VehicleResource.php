<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class VehicleResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'                => $this->id,
            'vehicle_type_id'   => (int)$this->vehicle_type_id,
            'vehicle_type'      => optional($this->type)->name,
            'branch_id'         => optional($this->branch)->id,
            'branch_name'       => optional($this->branch)->name,
            'route'             => $this->route,
            'employee_id'       => (int)$this->employee_id,
            'track'             => $this->track,
            'plate_number'      => $this->plate_number ?? null,
            'license_code'      => $this->license_code,
            'chassis_code'      => $this->chassis_code,
            'machine_code'      => $this->machine_code,
            'status'            => (int)$this->status,
            'driver'            =>  new EmployeeResource($this->whenLoaded('driver')),
            // Latest odometer and service info for mobile UI
            'current_km'        => optional($this->latestOdometer)->current_km,
            'last_km'           => optional($this->latestOdometer)->last_km,
            'last_service_date' => optional(optional($this->latestService)->date)?->format('Y-m-d'),
        ];
    }
}
