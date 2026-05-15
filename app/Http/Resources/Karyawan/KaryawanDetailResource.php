<?php

namespace App\Http\Resources\Karyawan;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class KaryawanDetailResource extends JsonResource
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
            'employee_id' => $this->employee_id,
            'employee' => [
                'id' => $this->employee?->id,
                'name' => $this->employee?->name,
                'department' => $this->employee?->department?->name,
                'branch' => $this->employee?->branch?->name,
                'contact' => $this->employee?->contact,
            ],
            'start_date' => Carbon::parse($this->start_date)->format('Y-m-d H:i'),
            'end_date' => Carbon::parse($this->end_date)->format('Y-m-d H:i'),
            'name' => $this->name,
            'salary' => $this->salary,
            'reason' => $this->reason,
            'status' => $this->status instanceof \BackedEnum ? $this->status->value : (string) $this->status,
            'approved_by' => $this->approved?->id,
            'approved' => [
                'id' => $this->approved?->id,
                'name' => $this->approved?->name,
                'department' => $this->approved?->department?->name,
            ],
            'approved_at' => Carbon::parse($this->approved_at)->format('Y-m-d H:i'),
            'notes' => $this->notes,
            'created_at' => Carbon::parse($this->created_at)->format('Y-m-d H:i'),
            'updated_at' => Carbon::parse($this->updated_at)->format('Y-m-d H:i'),
            'submission_date' => Carbon::parse($this->created_at)->format('Y-m-d'),
        ];
    }
}
