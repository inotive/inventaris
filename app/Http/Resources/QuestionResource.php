<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;


class QuestionResource extends JsonResource
{
    protected $employeeId;
    protected $inspectionId;
    protected $isProgress;

    public function __construct($resource, $employeeId = null, $inspectionId, $isProgress = false)
    {
        parent::__construct($resource);
        $this->employeeId = $employeeId;
        $this->inspectionId = $inspectionId;
        $this->isProgress = $isProgress;
    }

    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'checklist_id' => (int) $this->checklist_id,
            'category_id' => (int) $this->category_id,
            'employee_id' => (int) $this->employee_id,
            'category_name' => $this->category->name ?? null,
            'question' => $this->question,
            'required' => (bool) $this->required,
            'answer_type' => $this->answer_type,
            'guidance' => $this->guidance,
            'options' => $this->options->map(fn($opt) => [
                'id' => $opt->id,
                'label' => $opt->label,
                'value' => $opt->value,
            ])->values()->all(),

            // Add answers by employee_id
            'answers' => $this->isProgress ? $this->getAnswerByEmployee() : [],

            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }

    /**
     * Get answers for this question in the current inspection
     */
    private function getAnswerByEmployee()
    {
        \Log::info('QuestionResource::getAnswerByEmployee', [
            'question_id' => $this->id,
            'inspection_id' => $this->inspectionId,
            'isProgress' => $this->isProgress,
        ]);

        if (!$this->inspectionId) {
            return [];
        }

        if ($this->isProgress === "false" || $this->isProgress === false) {
            return [];
        }

        // Get all answers for this question in the current inspection
        return $this->answers()
            ->with('attachments')
            ->where('inspection_id', $this->inspectionId)
            ->get()
            ->map(function ($answer) {
                return [
                    'id' => $answer->id,
                    'answer' => $answer->answer,
                    'note' => $answer->note,
                    'employee_id' => (int) $answer->employee_id,
                    'attachment' => $answer->attachment,
                    'created_at' => $answer->created_at,
                    'updated_at' => $answer->updated_at,
                ];
            })
            ->values()
            ->all();
    }
}
