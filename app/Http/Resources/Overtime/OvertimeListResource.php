<?php

namespace App\Http\Resources\Overtime;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\Overtime\OvertimeEmployeeResource;
use Carbon\Carbon;

class OvertimeListResource extends JsonResource
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
            'date' => Carbon::parse($this->date)->format('d-m-Y'),
            'start_time' => Carbon::parse($this->start_time)->format('H:i'),
            'end_time' => Carbon::parse($this->end_time)->format('H:i'),
            // convert menit ke jam
            'duration_hours' => $this->duration_hours ? round($this->duration_hours / 60, 2) : 0,
            'reason' => $this->reason,
            'status' => $this->status,
            'status_label' => $this->status->label(),
            'approved_by' => $this->whenLoaded('approved', function () {
                return [
                    'id' => $this->approved->id,
                    'name' => $this->approved->name,
                ];
            }),
            'approved_at' => optional($this->approved_at ?? $this->updated_at)?->toDateTimeString(),
            'employee' => new OvertimeEmployeeResource($this->employee),
        ];
    }
}
