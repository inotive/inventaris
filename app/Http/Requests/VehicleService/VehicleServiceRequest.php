<?php

namespace App\Http\Requests\VehicleService;

use Illuminate\Foundation\Http\FormRequest;

class VehicleServiceRequest extends FormRequest
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
            'vehicle_id' => ['required', 'exists:vehicles,id'],
            'category_name'      => ['required', 'array'],
            'category_name.*'    => ['string'],
            'sub_category_name'  => ['required', 'array'],
            'sub_category_name.*' => ['string'],
            'cost'       => ['nullable', 'integer'],
            'date'       => ['required', 'date', 'before_or_equal:today'],
            'distance'   => ['nullable', 'integer'],
            'note'       => ['required', 'string', 'max:255'],
            'files'      => ['required', 'array'],
            'files.*'    => ['file', 'mimes:jpg,jpeg,png', 'max:5120'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'vehicle_id.required' => 'Kendaraan harus dipilih.',
            'vehicle_id.exists'   => 'Kendaraan yang dipilih tidak valid.',
            'category_name.required' => 'Kategori harus diisi.',
            'sub_category_name.required' => 'Sub Kategori harus diisi.',
            'cost.integer'        => 'Biaya harus berupa angka.',
            'date.required'       => 'Tanggal harus diisi.',
            'date.date'           => 'Format tanggal tidak valid.',
            'date.before_or_equal' => 'Tanggal tidak boleh melebihi tanggal hari ini.',
            'distance.integer'    => 'Jarak tempuh harus berupa angka.',
            'note.required'       => 'Catatan harus diisi.',
            'note.string'         => 'Catatan harus berupa teks.',
            'note.max'            => 'Catatan maksimal 255 karakter.',
            'files.required'      => 'File harus diisi.',
            'files.array'         => 'File harus berupa array.',
            'files.*.file'        => 'Setiap item harus berupa file.',
            'files.*.mimes'       => 'File harus berformat jpg, jpeg, atau png.',
            'files.*.max'         => 'Ukuran file maksimal 5120 KB (5 MB).',
        ];
    }
}
