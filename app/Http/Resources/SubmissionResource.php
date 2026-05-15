<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Enums\SubmissionStatusEnum;
use App\Enums\SubmissionTypeEnum;

class SubmissionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $statusEnum = $this->status instanceof SubmissionStatusEnum
            ? $this->status
            : SubmissionStatusEnum::from((int) $this->status);

        $typeEnum = $this->type instanceof SubmissionTypeEnum
            ? $this->type
            : SubmissionTypeEnum::from((int) $this->type);

        return [
            'id'               => $this->id,
            'employee_id'      => $this->employee_id,
            'branch_id'        => $this->branch_id,
            'status'           => $statusEnum->value,
            'status_label'     => $statusEnum->label(),
            'approved_by'      => $this->whenLoaded('approver', function () {
                return [
                    'id' => $this->approver->id,
                    'name' => $this->approver->name,
                ];
            }),
            'approved_at'      => optional($this->approved_at)?->toDateTimeString(),
            'type'             => $typeEnum->value,
            'type_label'       => $typeEnum->label(),
            'submission_date'  => $this->submission_date,
            'created_at'       => $this->created_at,
            'updated_at'       => $this->updated_at,
            'employee'         => new EmployeeResource($this->whenLoaded('employee')),
            'branch'           => $this->branch,
        ];
    }
}
