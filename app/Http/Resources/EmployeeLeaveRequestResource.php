<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class EmployeeLeaveRequestResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'employee_id' => $this->employee_id,
            'employee' => $this->whenLoaded('employee') ? new EmployeeResource($this->employee) : null,
            'leave_type_id' => $this->leave_type_id,
            'leave_type' => $this->whenLoaded('leaveType') ? new LeaveTypeResource($this->leaveType) : null,

            'start_date' => $this->start_date?->format('Y-m-d'),
            'end_date' => $this->end_date?->format('Y-m-d'),
            'total_days' => $this->total_days,
            'reason' => $this->reason,
            'admin_notes' => $this->admin_notes,

            'status' => $this->status?->value,

            'attachments' => is_array($this->attachment) ? array_map(function ($file) {
                return [
                    'file_name' => $file['file_name'] ?? null,
                    'file_url' => $file['file_url'] ?? null,
                    'file_path' => $file['file_path'] ?? null,
                    'file_type' => $file['file_type'] ?? null,
                    'file_size' => $file['file_size'] ?? null,
                ];
            }, $this->attachment) : [],

            'approved_by' => $this->whenLoaded('approver') ? new EmployeeResource($this->approver) : null,
            'approved_at' => optional($this->approved_at)->toDateTimeString(),
            'approvals' => $this->approvals,
            'created_at' => $this->created_at?->toDateTimeString(),
            'updated_at' => $this->updated_at?->toDateTimeString(),
        ];
    }
}
