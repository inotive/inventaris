<?php

namespace App\Http\Resources;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SubmissionAllResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        // Handle different submission types
        $type = $this->submission_type ?? 'unknown';

        // Common fields that might exist across different models
        $baseData = [
            'id' => $this->id,
            'submission_type' => $type,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'approved_at' => optional($this->approved_at ?? $this->updated_at)?->toDateTimeString(),
            'approved_by' => $this->resolveApprover(),
        ];

        // Format branch data
        $branch = null;
        if ($this->employee && $this->employee->branch) {
            $branch = [
                'id' => $this->employee->branch->id,
                'name' => $this->employee->branch->name,
            ];
        }

        // Add type-specific fields
        switch ($type) {
            case 'sick':
                return array_merge($baseData, [
                    'tanggal_pengajuan' => Carbon::parse($this->created_at)->format('Y-m-d'),
                    'status' => $this->status,
                    'employee' => new EmployeeResource($this->whenLoaded('employee')),
                    'branch' => $branch,
                    'type_label' => 'Sakit',
                ]);

            case 'overtime':
                return array_merge($baseData, [
                    'tanggal_pengajuan' => Carbon::parse($this->created_at)->format('Y-m-d'),
                    'status' => $this->status,
                    'employee' => new EmployeeResource($this->whenLoaded('employee')),
                    'branch' => $branch,
                    'type_label' => 'Lembur',
                ]);

            case 'employee':
                return array_merge($baseData, [
                    'tanggal_pengajuan' => Carbon::parse($this->created_at)->format('Y-m-d'),
                    'status' => $this->status,
                    'employee' => new EmployeeResource($this->whenLoaded('employee')),
                    'branch' => $branch,
                    'type_label' => 'Karyawan',
                ]);

            case 'debt':
                return array_merge($baseData, [
                    'tanggal_pengajuan' => Carbon::parse($this->created_at)->format('Y-m-d'),
                    'status' => $this->status ?? 'pending',
                    'employee' => new EmployeeResource($this->whenLoaded('employee')),
                    'branch' => $branch,
                    'type_label' => 'Piutang',
                ]);

            case 'reimbursement':
                return array_merge($baseData, [
                    'tanggal_pengajuan' => Carbon::parse($this->created_at)->format('Y-m-d'),
                    'status' => $this->status ?? 'pending',
                    'employee' => new EmployeeResource($this->whenLoaded('employee')),
                    'branch' => $branch,
                    'type_label' => 'Reimbursement',
                    'title' => $this->title ?? null,
                    'event_date' => $this->event_date ? Carbon::parse($this->event_date)->format('Y-m-d') : null,
                    'amount' => $this->amount ?? 0,
                    'currency' => $this->currency ?? 'IDR',
                    'admin_notes' => $this->admin_notes ?? null,
                ]);

            default:
                return array_merge($baseData, [
                    'tanggal_pengajuan' => Carbon::parse($this->created_at)->format('Y-m-d'),
                    'status' => $this->status ?? 'pending',
                    'employee' => new EmployeeResource($this->whenLoaded('employee')),
                    'branch' => $branch,
                    'type_label' => ucfirst($type),
                ]);
        }
    }

    private function resolveApprover(): ?array
    {
        $approver = $this->approver ?? $this->approvedBy ?? $this->approved ?? null;

        if (!$approver) {
            return null;
        }

        return [
            'id' => $approver->id ?? null,
            'name' => $approver->name ?? null,
        ];
    }
}
