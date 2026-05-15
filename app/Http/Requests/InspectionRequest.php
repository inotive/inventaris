<?php

namespace App\Http\Requests;

use Illuminate\Container\Attributes\Auth;
use Illuminate\Foundation\Http\FormRequest;
use Opcodes\LogViewer\Logs\Log;

class InspectionRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {

        \Log::info("InspectionRequest", ['data' => $this->all()]);

        return [
            'inspection_id'     => 'nullable|integer|exists:inspections,id',
            'checklist_id'      => 'required|integer|exists:checklists,id',
            'submit_date'       => 'nullable|date',
            'status'            => 'required|in:draft,on_progress,submitted,approved,rejected',
            'remarks'           => 'nullable|string',
            'location_id'       => 'nullable|integer|exists:branches,id',
            'model_type'        => 'nullable|string|in:vehicle,employee',
            'model_id'          => 'nullable|integer',
            'answers' => 'nullable|array',
            'answers.*.answer_id' => 'nullable|integer',
            'answers.*.question_id' => 'required|integer|exists:questions,id',
            'answers.*.answer' => 'required|string|max:1000',
            'answers.*.note' => 'nullable|string',
            'answers.*.attachment' => 'nullable|string',
        ];


    }


    /**
     * Get custom error messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'answers.required' => 'At least one answer is required.',
            'answers.*.answer_id.integer' => 'Answer ID must be an integer.',
            'answers.*.answer_id.exists' => 'The selected answer does not exist.',
            'answers.*.question_id.required' => 'Question ID is required for each answer.',
            'answers.*.question_id.integer' => 'Question ID must be an integer.',
            'answers.*.question_id.exists' => 'The selected question does not exist.',
            'answers.*.employee_id.integer' => 'Employee ID must be an integer.',
            'answers.*.employee_id.exists' => 'The selected employee does not exist.',
            'answers.*.answer.required' => 'Answer text is required.',
            'answers.*.answer.string' => 'Answer must be a string.',
            'answers.*.answer.max' => 'Answer cannot exceed 1000 characters.',
        ];
    }
}
