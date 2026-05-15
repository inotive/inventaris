<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class LeaveTypeResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'                   => $this->id,
            'name'                 => $this->name,
            'category'             => $this->category?->value ?? null, // enum value
            'description'          => $this->description,
            'leave_quota_per_year' => $this->leave_quota_per_year,
            'created_at'           => $this->created_at ? $this->created_at->toDateTimeString() : null,
            'updated_at'           => $this->updated_at ? $this->updated_at->toDateTimeString() : null,
        ];
    }
}
