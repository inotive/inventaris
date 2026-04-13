<?php

namespace App\Http\Resources\Debt;

use App\Http\Resources\Sick\SickEmployeeResource;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ReceivableEmployeeDetailResource extends JsonResource
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
            'date' => Carbon::parse($this->date)->format('d-m-Y'),
            'amount' => $this->amount,
            'tenor' => $this->tenor,
            'note' => $this->note,
            'file_path' => $this->file_path,
            'status' => $this->status,
            'status_label' => $this->status->label(),
            'admin_notes' => $this->admin_notes,
            'approved_at' => $this->approved_at ? Carbon::parse($this->approved_at)->format('d-m-Y H:i') : null,
            'approved_by' => $this->whenLoaded('approver', function () {
                return [
                    'id' => $this->approver->id,
                    'name' => $this->approver->name,
                ];
            }),
            'employee' => new SickEmployeeResource($this->whenLoaded('employee')),
        ];
    }
}
