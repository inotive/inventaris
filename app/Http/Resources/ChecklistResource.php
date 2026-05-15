<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ChecklistResource extends JsonResource
{
    protected $employeeId;
    protected $inspectionId;
    protected $isProgress;

    public function __construct($resource, $employeeId = null, $inspectionId = null, $isProgress = false)
    {
        parent::__construct($resource);
        $this->employeeId = $employeeId;
        $this->inspectionId = $inspectionId;
        $this->isProgress = $isProgress;
    }

    public function toArray($request)
    {
        // dd($this->id);
        $data = [
            'id'          => $this->id,
            'name'        => $this->name,
            'code_sop'    => $this->code_sop,
            'status'      => $this->status,
            'description' => $this->description,
            'category_id'   => $this->category->id,
            'category_name' => $this->category->name,
            'type' => $this->type == "Banyak Orang" ? "multiple" : "single",
            // 'branch_id'   => $this->branch->id,
            // 'branch_name' => $this->branch->name,
            'created_at'  => $this->created_at,
            'updated_at'  => $this->updated_at,
        ];

        if ($this->relationLoaded('questions')) {
            $data['questions'] = $this->questions->map(function ($question) {
                return new QuestionResource($question, $this->employeeId, $this->inspectionId, $this->isProgress);
            });
        }

        return $data;
    }
}
