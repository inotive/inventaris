<?php

namespace App\Http\Requests\Submission;

use Illuminate\Foundation\Http\FormRequest;

class ProcurementUpdateRequest extends FormRequest
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
            'requested_at' => 'sometimes|required|date',
            'requirement' => 'sometimes|required|string',
            'note' => 'sometimes|nullable|string',
            'items' => 'sometimes|required|array',
            'items.*.item_id' => 'sometimes|required|exists:items,id',
            'items.*.quantity' => 'sometimes|required|integer|min:1',
            // 'items.*.note' => 'sometimes|nullable|string',
        ];
    }
}
