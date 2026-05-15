<?php

namespace App\Http\Requests\VehicleDocument;

use Illuminate\Foundation\Http\FormRequest;

class CreateVehicleDocumentRequest extends FormRequest
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
            'vehicle_id'    => 'required|integer|exists:vehicles,id',
            'name'          => 'required|string|max:255',
            'renewal_date'  => 'required|date',
            'expired_date'  => 'required|date|after:renewal_date',
            'files'         => 'required|array|min:1',
            'files.*'       => 'required|file|mimes:jpg,jpeg,png|max:5120',
        ];
    }

    public function messages(): array
    {
        return [
            'vehicle_id.required' => 'Kendaraan wajib dipilih.',
            'vehicle_id.integer' => 'ID kendaraan harus berupa angka.',
            'vehicle_id.exists' => 'Kendaraan yang dipilih tidak valid.',
            
            'name.required' => 'Nama dokumen wajib diisi.',
            'name.max' => 'Nama dokumen maksimal 255 karakter.',
            
            'renewal_date.required' => 'Tanggal perpanjangan wajib diisi.',
            'renewal_date.date' => 'Tanggal perpanjangan harus berupa tanggal yang valid.',
            
            'expired_date.required' => 'Tanggal kadaluarsa wajib diisi.',
            'expired_date.date' => 'Tanggal kadaluarsa harus berupa tanggal yang valid.',
            'expired_date.after' => 'Tanggal kadaluarsa harus setelah tanggal perpanjangan.',
            
            'files.required' => 'Gambar dokumen wajib diupload.',
            'files.array' => 'Format file tidak valid.',
            'files.min' => 'Minimal 1 gambar dokumen harus diupload.',
            'files.*.required' => 'File gambar wajib diisi.',
            'files.*.file' => 'File yang diupload harus berupa file.',
            'files.*.mimes' => 'File harus berformat JPG, JPEG, atau PNG.',
            'files.*.max' => 'Ukuran file maksimal 5MB.',
        ];
    }

    public function attributes(): array
    {
        return [
            'vehicle_id' => 'kendaraan',
            'name' => 'nama dokumen',
            'renewal_date' => 'tanggal perpanjangan',
            'expired_date' => 'tanggal kadaluarsa',
            'files' => 'gambar dokumen',
        ];
    }
}
