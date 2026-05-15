<?php

namespace App\Http\Requests\Employee;

use Illuminate\Foundation\Http\FormRequest;

class StoreEmployeeRequest extends FormRequest
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
            'username'           => 'required|string|max:255|unique:users,username',
            'email'              => 'required|string|email|max:255|unique:users,email',
            'password'           => 'required|string|min:8',
            'name'               => 'required|string|max:255',
            'branch_id'          => 'required|exists:branches,id',
            'department_id'      => 'required|exists:departments,id',
            'jabatan_id'         => 'nullable|exists:roles,id',
            'position_id'        => 'nullable|exists:positions,id',
            'shift_id'           => 'required|exists:shifts,id',
            'working_start_date' => 'required|date',
            'salary'             => 'nullable|numeric|min:1',
            'photo'              => 'required|image|mimes:jpeg,jpg,png|max:10240',
            // quotas (now required per business rule)
            'leave_quota_per_year' => 'required|integer|min:0',
            'loan_quota'           => 'nullable|integer|min:0',
            // new fields
            'birthdate'               => 'required|date',
            'signature'                => 'nullable|image|mimes:jpeg,jpg,png|max:10240',
            // optional profile fields
            'contact'                  => 'nullable|string|max:50',
            'address'                  => 'nullable|string',
            'provinsi'                 => 'nullable|string|max:255',
            'kota'                     => 'nullable|string|max:255',
            'kecamatan'                => 'nullable|string|max:255',
            'kelurahan'                => 'nullable|string|max:255',
            'gender'                   => 'required|string|max:20',
            'status'                   => 'required|string|max:50',
            'birthplace'               => 'nullable|string|max:100',
            'religion'                 => 'nullable|string|max:50',
            'nik'                      => 'nullable|string|max:100',
            'ktp'                      => 'nullable|string|max:100',
            'bpjs_kesehatan'           => 'nullable|string|max:100',
            'bpjs_ketenagakerjaan'     => 'nullable|string|max:100',
            'certificate'              => 'nullable|string',
            'contract'                 => 'nullable|string',
            // document uploads or links
            'ktp_file'                 => 'nullable|file|mimes:jpeg,jpg,png,pdf|max:10240',
            'ktp_url'                  => 'nullable|url',
            'bpjs_kesehatan_file'      => 'nullable|file|mimes:jpeg,jpg,png,pdf|max:10240',
            'bpjs_kesehatan_url'       => 'nullable|url',
            'bpjs_ketenagakerjaan_file' => 'nullable|file|mimes:jpeg,jpg,png,pdf|max:10240',
            'bpjs_ketenagakerjaan_url' => 'nullable|url',
            // certificate & contract uploads
            'certificate_file'         => 'nullable|file|mimes:jpeg,jpg,png,pdf,doc,docx|max:10240',
            'certificate_url'          => 'nullable|url',
            'contract_file'            => 'nullable|file|mimes:jpeg,jpg,png,pdf,doc,docx|max:10240',
            'contract_url'             => 'nullable|url',
            'kk_file'                  => 'nullable|file|mimes:jpeg,jpg,png,pdf|max:10240',
            'kk_url'                   => 'nullable|url',
            'is_add_more'              => 'nullable|boolean',
            'resign_date'              => 'nullable|date',
        ];
    }

    public function messages(): array
    {
        return [
            'required'     => ':attribute wajib diisi.',
            'unique'       => ':attribute sudah digunakan.',
            'exists'       => ':attribute tidak ditemukan.',
            'email'        => 'Format :attribute tidak valid.',
            'date'         => 'Format :attribute tidak valid.',
            'numeric'      => ':attribute harus berupa angka.',
            'min.string'   => ':attribute minimal :min karakter.',
            'min.numeric'  => ':attribute minimal :min.',
            'image'        => 'File :attribute harus berupa gambar.',
            'mimes'        => ':attribute harus berformat: :values.',
            'max.file'     => 'Ukuran :attribute maksimal :max KB.',
            'max.string'   => 'Panjang :attribute maksimal :max karakter.',
            'photo.max'    => 'Ukuran foto maksimal 12mb.',
            'salary.min'   => 'Gaji pokok minimal lebih dari 0.',
        ];
    }

    public function attributes(): array
    {
        return [
            'username'            => 'Username',
            'email'               => 'Email',
            'password'            => 'Password',
            'name'                => 'Nama',
            'branch_id'           => 'Cabang',
            'department_id'       => 'Departemen',
            'jabatan_id'          => 'Jabatan',
            'position_id'         => 'Position',
            'shift_id'            => 'Shift',
            'working_start_date'  => 'Tanggal mulai kerja',
            'salary'              => 'Gaji',
            'photo'               => 'Foto',
            'birth_date'          => 'Tanggal Lahir',
            'signature'           => 'Tanda Tangan',
            'ktp_file'            => 'File KTP',
            'ktp_url'             => 'Link KTP',
            'bpjs_kesehatan_file' => 'File BPJS Kesehatan',
            'bpjs_kesehatan_url'  => 'Link BPJS Kesehatan',
            'bpjs_ketenagakerjaan_file' => 'File BPJS Ketenagakerjaan',
            'bpjs_ketenagakerjaan_url'  => 'Link BPJS Ketenagakerjaan',
            'certificate_file'          => 'File Sertifikat',
            'certificate_url'           => 'Link Sertifikat',
            'contract_file'             => 'File Kontrak',
            'contract_url'              => 'Link Kontrak',
            'kk_file'                   => 'File Kartu Keluarga',
            'kk_url'                    => 'Link Kartu Keluarga',
            'resign_date'               => 'Tanggal Resign',
        ];
    }
}
