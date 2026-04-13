<?php

namespace App\Http\Resources\Sick;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SickEmployeeDetailResource extends JsonResource
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
            'leave_type_name' => optional($this->leaveType)->name,
            'tanggal_pengajuan' => Carbon::parse($this->created_at)->format('d-m-Y H:i'),
            'start_date' => Carbon::parse($this->start_date)->format('d-m-Y'),
            'end_date' => Carbon::parse($this->end_date)->format('d-m-Y'),
            'total_days' => $this->total_days,
            'status' => $this->status,
            'status_label' => $this->status->label(),
            'employee' => new SickEmployeeResource($this->employee),
            'approvals' => new SickEmployeeResource($this->approvals),
            'attachment' => $this->attachment,
            'reason' => $this->reason,
            'admin_notes' => $this->admin_notes,
            'approved_at' => Carbon::parse($this->approved_at)->format('d-m-Y H:i'),
            // Correction fields
            'corrected_start_date' => $this->corrected_start_date ? Carbon::parse($this->corrected_start_date)->format('d-m-Y') : null,
            'corrected_end_date' => $this->corrected_end_date ? Carbon::parse($this->corrected_end_date)->format('d-m-Y') : null,
            'corrected_at' => $this->corrected_at ? Carbon::parse($this->corrected_at)->format('d-m-Y H:i') : null,
            'corrected_by' => $this->correctedBy ? $this->correctedBy->name : null,
            'correction_reason' => $this->correction_reason,
        ];
    }
}
