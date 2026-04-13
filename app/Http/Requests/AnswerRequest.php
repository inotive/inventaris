<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AnswerRequest extends FormRequest
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
        return [
            'answers' => 'required|array|min:1',
            'answers.*.answer_id' => 'nullable|integer|exists:answers,id',
            'answers.*.question_id' => 'required|integer|exists:questions,id',
            'answers.*.employee_id' => 'nullable|integer|exists:users,id',
            'answers.*.answer' => 'required|string|max:1000',
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

    /**
     * Get custom attributes for validator errors.
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'answers.*.answer_id' => 'answer ID',
            'answers.*.question_id' => 'question ID',
            'answers.*.employee_id' => 'employee ID',
            'answers.*.answer' => 'answer',
        ];
    }
}
