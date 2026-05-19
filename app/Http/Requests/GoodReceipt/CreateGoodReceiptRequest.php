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
            'attachment' => 'nullable|file|mimes:pdf,jpg,jpeg,png,doc,docx|max:10240',
            'attachments' => 'nullable|array',
            'attachments.*' => 'file|mimes:pdf,jpg,jpeg,png,doc,docx|max:10240',
            'items' => 'required|array',
            'items.*.item_id' => 'required|exists:items,id',
            'items.*.quantity_received' => 'required|integer',
            'items.*.note' => 'nullable|string',
            'items.*.shortage_reason' => 'nullable|in:rusak,hilang',
            'items.*.shortage_description' => 'nullable|string|max:500',
            'items.*.shortage_quantity' => 'nullable|integer|min:1',
            'items.*.shortages' => 'nullable|array',
            'items.*.shortages.*.reason' => 'required|in:rusak,hilang',
            'items.*.shortages.*.quantity' => 'required|integer|min:1',
            'items.*.shortages.*.description' => 'nullable|string|max:500',
        ];
    }
}
