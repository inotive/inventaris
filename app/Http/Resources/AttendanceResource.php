<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class AttendanceResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'employee' => [
                'id' => $this->employee->id ?? null,
                'name' => $this->employee->name ?? null,
                'position' => $this->employee->position ?? null,
                'shift' => [
                    'id' => $this->shift->id ?? null,
                    'name' => $this->shift->name ?? null,
                ],
            ],
            'tanggal' => $this->tanggal,
            'jam_masuk' => $this->jam_masuk,
            'jam_keluar' => $this->jam_keluar,
            'status' => $this->status,
            'keterangan' => $this->keterangan,
            'is_face_correct' => $this->is_face_correct,
            'created_at' => $this->created_at->toDateTimeString(),
            'updated_at' => $this->updated_at->toDateTimeString(),
        ];
    }
}
