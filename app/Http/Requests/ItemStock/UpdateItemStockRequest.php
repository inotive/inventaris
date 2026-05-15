<?php

namespace App\Http\Requests\ItemStock;

use Illuminate\Foundation\Http\FormRequest;

class UpdateItemStockRequest extends FormRequest
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
            'item_id' => 'required|exists:items,id',
            'type' => 'required|in:in,out',
            'quantity' => 'required|integer',
            'tanggal' => 'nullable|date',
            'note' => 'nullable|string',
        ];
    }
}
