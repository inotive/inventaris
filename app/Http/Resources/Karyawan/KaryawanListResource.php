<?php

namespace App\Http\Resources\Karyawan;

use App\Http\Resources\Sick\SickEmployeeResource;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class KaryawanListResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'tanggal_pengajuan' => Carbon::parse($this->created_at)->format('d-m-Y'),
            'start_date' => Carbon::parse($this->start_date)->format('d-m-Y'),
            'end_date' => Carbon::parse($this->end_date)->format('d-m-Y'),
            'total_days' => $this->total_days,
            'name' => $this->name,
            'salary' => $this->salary,
            'status' => $this->status instanceof \BackedEnum ? $this->status->value : (string) $this->status,
            'status_label' => $this->status instanceof \BackedEnum ? $this->status->label() : (string) $this->status,
            'reason' => $this->reason,
            'notes' => $this->notes,
            'approved_by' => $this->whenLoaded('approved', function () {
                return [
                    'id' => $this->approved->id,
                    'name' => $this->approved->name,
                ];
            }),
            'approved_at' => optional($this->approved_at ?? $this->updated_at)?->toDateTimeString(),
            'employee' => new SickEmployeeResource($this->employee),
        ];
    }
}
