<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ApproveProcurement extends FormRequest
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
            'approvals' => 'required|array',
            'approvals.*.id' => 'required|exists:material_request_items,id',
            'approvals.*.quantity_requested' => 'required|exists:items,id',
            'approvals.*.quantity_approved' => 'required|numeric|min:0',
            'approvals.*.note' => 'nullable|string|max:255',
        ];
    }
}
