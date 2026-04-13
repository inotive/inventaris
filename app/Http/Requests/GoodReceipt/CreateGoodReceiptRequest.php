<?php

namespace App\Http\Requests\GoodReceipt;

use Illuminate\Foundation\Http\FormRequest;

class CreateGoodReceiptRequest extends FormRequest
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
            'source' => 'required|in:Pembelian,Pemindahan',
            'employee_id' => 'required|exists:employees,id',
            'order_id' => 'nullable|exists:purchase_orders,id',
            'transfer_id' => 'nullable|exists:good_transfers,id',
            'note' => 'nullable|string',
            'items' => 'required|array',
            'items.*.item_id' => 'required|exists:items,id',
            'items.*.quantity_received' => 'required|integer',
            'items.*.note' => 'nullable|string',
        ];
    }
}
