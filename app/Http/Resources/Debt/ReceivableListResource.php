<?php

namespace App\Http\Resources\Debt;

use App\Http\Resources\Sick\SickEmployeeResource;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ReceivableListResource extends JsonResource
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
            'start_date' => Carbon::parse($this->start_date)->format('d-m-Y'),
            'end_date' => Carbon::parse($this->end_date)->format('d-m-Y'),
            'total_days' => $this->total_days,
            'tenor' => $this->tenor,
            'amount' => $this->amount,
            'remaining_amount' => $this->payments->sum('amount'),
            'status' => $this->status,
            'status_label' => $this->status->label(),
            'approved_by' => $this->whenLoaded('approver', function () {
                return [
                    'id' => $this->approver->id,
                    'name' => $this->approver->name,
                ];
            }),
            'approved_at' => optional($this->approved_at ?? $this->updated_at)?->toDateTimeString(),
            'employee' => new SickEmployeeResource($this->employee),
        ];
    }
}
