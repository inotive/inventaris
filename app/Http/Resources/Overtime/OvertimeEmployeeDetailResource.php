<?php

namespace App\Http\Resources\Overtime;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OvertimeEmployeeDetailResource extends JsonResource
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
            'tanggal_pengajuan' => Carbon::parse($this->created_at)->format('d-m-Y H:i'),
            'date' => Carbon::parse($this->date)->format('d-m-Y'),
            'start_time' => Carbon::parse($this->start_time)->format('H:i'),
            'end_time' => Carbon::parse($this->end_time)->format('H:i'),
            'duration_hours' => $this->durationHours($this->duration_hours),
            'reason' => $this->reason,
            'status' => $this->status,
            'status_label' => $this->status->label(),
            'employee' => new OvertimeEmployeeResource($this->employee),
            'approved_by' => $this->approved ? new OvertimeEmployeeResource($this->approved) : null,
            'notes' => $this->notes,
            'approved_at' => $this->updated_at ? Carbon::parse($this->updated_at)->format('d-m-Y H:i') : null,
        ];
    }


    /**
     * Mengubah durasi (menit) ke format jam-menit yang mudah dibaca manusia
     * Contoh: 125 -> "2 jam 5 menit"
     */
    public function durationHours($duration_minutes)
    {
        $hours = floor($duration_minutes / 60);
        $minutes = $duration_minutes % 60;
        $parts = [];
        if ($hours > 0) {
            $parts[] = $hours . ' jam';
        }
        if ($minutes > 0) {
            $parts[] = $minutes . ' menit';
        }
        return count($parts) > 0 ? implode(' ', $parts) : '0 menit';
    }
}
