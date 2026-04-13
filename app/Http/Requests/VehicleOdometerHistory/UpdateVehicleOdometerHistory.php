<?php

namespace App\Http\Requests\VehicleOdometerHistory;

use Illuminate\Foundation\Http\FormRequest;

class UpdateVehicleOdometerHistory extends FormRequest
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
            'vehicle_id' => 'required|exists:vehicles,id',
            'inspection_id' => 'nullable|exists:inspections,id',
            'current_km' => 'required|integer',
            'tanggal' => 'nullable|date',
        ];
    }
}
