<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Auth;

class InspectionResource extends JsonResource
{
    protected $employeeId;
    protected $withAnswers;

    public function __construct($resource, $employeeId = null, $withAnswers = false)
    {
        parent::__construct($resource);
        $this->employeeId = $employeeId;
        $this->withAnswers = $withAnswers;
    }

    public function toArray(Request $request): array
    {
        $data = [
            'id' => $this->id,
            'checklist_id' => $this->checklist_id,
            'inspection_number' => $this->inspection_number,
            'submit_date' => $this->submit_date,
            'submitted_by' => $this->submitted_by,
            'status' => $this->generateStatusLabel(),
            'status_label' => $this->generateStatusLabel(),
            'approved_by' => $this->approved_by,
            'approved_date' => $this->approved_date,
            'remarks' => $this->remarks,
            'location_id' => $this->location_id,
            'location' => new BranchResource($this->whenLoaded('location')),
            'model_type' => $this->mapModelType($this->model_type),
            'model_id' => $this->model_id,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];

        if ($this->relationLoaded('submittedBy')) {
            $data['submitted_by'] = new UserResource($this->submittedBy);
        }

        if ($this->relationLoaded('checklist')) {
            // Pass employeeId, inspectionId, and withAnswers to ChecklistResource
            $data['checklist'] = new ChecklistResource(
                $this->checklist,
                $this->employeeId,
                $this->id,
                $this->withAnswers
            );
        }

        // Tambahkan field khusus berdasarkan model_type
        if ($this->relationLoaded('model')) {
            if ($this->model_type === \App\Models\Vehicle::class) {
                $data['vehicle'] = new VehicleResource($this->model);
            } elseif ($this->model_type === \App\Models\Employee::class) {
                $data['employee'] = new EmployeeResource($this->model);
            }
        }

        return $data;
    }


    private function mapModelType(?string $type): ?string
    {
        return match ($type) {
            \App\Models\Vehicle::class => 'vehicle',
            \App\Models\Employee::class => 'employee',
            default => null,
        };
    }

    private function mapStatusLabel(?string $status): ?string
    {
        return match (strtolower($status ?? '')) {
            'submitted' => 'Selesai',
            'draft' => 'Belum Selesai',
            'on_progress' => 'Sedang Berjalan',
            'approved' => 'Disetujui',
            'rejected' => 'Ditolak',
            default => $status,
        };
    }



    private function generateStatusLabel()
    {
        if ($this->status === 'on_progress') {
            $answers = $this->answers()->where('employee_id', Auth::user()->employee->id)->exists() ? "Selesai" : "Sedang Berjalan";
            return $answers;
        } else {
            return $this->mapStatusLabel($this->status);
        }
    }
}
