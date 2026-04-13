<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EmployeeResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'                    => $this->id,
            'staff_id'              => $this->staff_id,
            'name'                  => $this->name,
            'contact'               => $this->contact,
            'address'               => $this->address,
            'status'                => $this->status,
            'gender'                => $this->gender,
            'birthplace'            => $this->birthplace,
            'religion'              => $this->religion,
            'nik'                   => $this->nik,
            'ktp'                   => $this->ktp,
            'bpjs_kesehatan'        => $this->bpjs_kesehatan,
            'bpjs_ketenagakerjaan'  => $this->bpjs_ketenagakerjaan,
            'certificate'           => $this->certificate,
            'contract'              => $this->contract,
            'verification'          => $this->verification,
            'branch_id'             => $this->branch?->id,
            'branch_name'           => $this->branch?->name,
            'department_id'         => $this->department?->id,
            'department_name'       => $this->department?->name,
            'created_at'            => $this->created_at,
            'updated_at'            => $this->updated_at,
        ];
    }
}
