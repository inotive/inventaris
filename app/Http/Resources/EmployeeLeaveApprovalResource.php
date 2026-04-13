<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EmployeeLeaveApprovalResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'                        => $this->id,
            'leave_request_id'          => $this->leave_request_id,
            'leave_request'             => new EmployeeLeaveRequestResource($this->whenLoaded('leaveRequest')),
            'approved_id'               => $this->approved_id,
            'approved'                  => new EmployeeResource($this->whenLoaded('approved')),
            'level'                     => $this->level,
            'status'                    => $this->status,
            'notes'                     => $this->notes,
            'approved_at'               => $this->approved_at?->toDateTimeString(),
            'created_at'                => $this->created_at?->toDateTimeString(),
            'updated_at'                => $this->updated_at?->toDateTimeString(),
        ];
    }
}
