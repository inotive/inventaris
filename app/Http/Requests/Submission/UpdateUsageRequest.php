<?php

namespace App\Http\Requests\Submission;

use Illuminate\Foundation\Http\FormRequest;

class UpdateUsageRequest extends FormRequest
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
            'date' => 'sometimes|required|date',
            'notes' => 'sometimes|nullable|string',
            'items' => 'sometimes|required|array',
            'items.*.id' => 'sometimes|required|integer',
            'items.*.quantity' => 'sometimes|required|integer',
            // 'items.*.notes' => 'sometimes|nullable|string',
        ];
    }
}
