<?php

namespace App\Http\Resources\Sick;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\Sick\SickEmployeeResource;
use Carbon\Carbon;

class SickListResource extends JsonResource
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
            'leave_type_name' => optional($this->leaveType)->name,
            'status' => $this->status,
            'status_label' => $this->status->label(),
            'approved_by' => $this->whenLoaded('approver', function () {
                return [
                    'id' => $this->approver->id,
                    'name' => $this->approver->name,
                ];
            }),
            'approved_at' => optional($this->approved_at ?? $this->updated_at)?->toDateTimeString(),
            'employee' => new SickEmployeeResource($this->employee),
            'attachment' => $this->attachment,
        ];
    }
}
