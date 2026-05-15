<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SalaryResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $baseUrl = rtrim(config('app.url'), '/');
        return [
            'id' => $this->id,
            'employee_id' => $this->employee_id,
            'bulan' => $this->bulan,
            'file_url' => $baseUrl . ($this->file_url ?? ''),
            'uploaded_by' => $this->upload_by,
            'created_at' => $this->created_at?->toDateTimeString(),
            'updated_at' => $this->updated_at?->toDateTimeString(),
        ];
    }
}
