<?php

namespace App\Http\Requests\Vehicle;

use Illuminate\Foundation\Http\FormRequest;

class UpdateVehicleRequest extends FormRequest
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
        $vehicleId = $this->route('vehicle')->id;
        return [
            'vehicle_type_id' => ['required'],
            'branch_id'       => 'required|integer|exists:branches,id',
            'employee_id'     => 'required|integer|exists:employees,id',
            'track'           => 'required|string|max:100',
            'license_code'    => 'required|string|unique:vehicles,license_code,' . $vehicleId,
            'chassis_code'    => 'required|string|unique:vehicles,chassis_code,' . $vehicleId,
            'machine_code'    => 'required|string|unique:vehicles,machine_code,' . $vehicleId,
            'status'          => 'required|boolean',
            'files'           => 'nullable|array',
            'files.*'         => 'nullable|file|mimes:jpg,jpeg,png|max:5120',
            'deleted_images'  => 'nullable|array',
        ];
    }

    /**
     * Configure the validator instance.
     *
     * @param  \Illuminate\Validation\Validator  $validator
     * @return void
     */
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $vehicle = $this->route('vehicle');
            $deletedImages = $this->input('deleted_images', []);
            $newFiles = $this->file('files', []);
            $currentFilesCount = $vehicle->files()->count();
            $deletedCount = is_array($deletedImages) ? count($deletedImages) : 0;
            $newFilesCount = is_array($newFiles) ? count($newFiles) : 0;

            // Calculate remaining files after deletion
            $remainingFiles = $currentFilesCount - $deletedCount;

            // If all existing files are deleted and no new files are uploaded, validation fails
            if ($remainingFiles <= 0 && $newFilesCount === 0) {
                $validator->errors()->add('files', 'Minimal harus ada 1 foto kendaraan. Silakan tambahkan foto baru atau batalkan penghapusan foto yang ada.');
            }
        });
    }

    public function messages(): array
    {
        return [
            'vehicle_type_id.required' => 'Tipe kendaraan wajib dipilih.',
            'vehicle_type_id.integer' => 'Tipe kendaraan harus berupa angka.',
            'vehicle_type_id.exists' => 'Tipe kendaraan yang dipilih tidak valid.',
            'branch_id.required' => 'Cabang wajib dipilih.',
            'branch_id.integer' => 'Cabang harus berupa angka.',
            'branch_id.exists' => 'Cabang yang dipilih tidak valid.',
            'employee_id.required' => 'Driver wajib dipilih.',
            'employee_id.integer' => 'Driver harus berupa angka.',
            'employee_id.exists' => 'Driver yang dipilih tidak valid.',
            'track.required' => 'Rute penugasan wajib diisi.',
            'track.max' => 'Rute penugasan maksimal 100 karakter.',
            'license_code.required' => 'Nomor plat kendaraan wajib diisi.',
            'license_code.unique' => 'Nomor plat kendaraan sudah terdaftar.',
            'chassis_code.required' => 'Nomor rangka kendaraan wajib diisi.',
            'chassis_code.unique' => 'Nomor rangka kendaraan sudah terdaftar.',
            'machine_code.required' => 'Nomor mesin kendaraan wajib diisi.',
            'machine_code.unique' => 'Nomor mesin kendaraan sudah terdaftar.',
            'status.required' => 'Status kendaraan wajib dipilih.',
            'files.*.file' => 'File yang diupload harus berupa file.',
            'files.*.mimes' => 'File harus berformat JPG, JPEG, atau PNG.',
            'files.*.max' => 'Ukuran file maksimal 5MB.',
            'files.required' => 'Minimal harus ada 1 foto kendaraan. Silakan tambahkan foto baru atau batalkan penghapusan foto yang ada.',
        ];
    }

    public function attributes(): array
    {
        return [
            'vehicle_type_id' => 'tipe kendaraan',
            'branch_id' => 'cabang',
            'employee_id' => 'driver',
            'track' => 'rute',
            'license_code' => 'nomor plat',
            'chassis_code' => 'nomor rangka',
            'machine_code' => 'nomor mesin',
            'status' => 'status',
            'files' => 'foto kendaraan',
        ];
    }
}
