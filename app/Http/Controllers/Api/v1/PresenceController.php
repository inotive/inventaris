<?php

namespace App\Http\Controllers\Api\v1;

use App\Helpers\ResponseFormatter;
use App\Http\Controllers\Controller;
use App\Models\AbsenceArea;
use App\Models\Attendance;
use App\Models\Employee;
use Illuminate\Http\Request;

class PresenceController extends Controller
{
    public function store(Request $request): \Illuminate\Http\JsonResponse
    {
        $rules = [
            'date_timestamp' => ['required', 'integer'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'photo' => ['nullable', 'image', 'mimes:jpeg,png,jpg', 'max:2048'],
        ];

        $messages = [
            'date_timestamp.required' => 'Token absensi diperlukan.',
            'date_timestamp.integer' => 'Token absensi tidak valid.',
            'latitude.numeric' => 'Latitude harus berupa angka.',
            'latitude.between' => 'Latitude harus antara -90 dan 90.',
            'longitude.numeric' => 'Longitude harus berupa angka.',
            'longitude.between' => 'Longitude harus antara -180 dan 180.',
            'photo.image' => 'File harus berupa gambar.',
            'photo.mimes' => 'Format gambar harus jpeg, png, atau jpg.',
            'photo.max' => 'Ukuran gambar maksimal 2MB.',
        ];

        $attributes = [
            'date_timestamp' => 'Token absensi',
            'latitude' => 'Latitude',
            'longitude' => 'Longitude',
            'photo' => 'Foto',
        ];

        $validator  = \Validator::make($request->all(), $rules, $messages, $attributes);
        if ($validator->fails()) {
            return ResponseFormatter::error($validator->errors()->first(), 400);
        }

        $employee = Employee::with([
            'shift',
            'department:id,name',
            'branch:id,name',
            'role:id,name',
        ])->find(auth()->user()->employee->id);

        if (!$employee) {
            return ResponseFormatter::error('Employee not found', 404);
        }

        // Fix: Check if AbsenceArea is NOT set for this branch (should be !exists)
        $checkRadius = !AbsenceArea::where('branch_id', $employee->branch_id)->exists();

        if ($checkRadius) {
            return ResponseFormatter::error('Fitur absensi dengan radius lokasi belum diaktifkan. Silakan hubungi atasan atau admin untuk konfirmasi.', 400);
        }

        $tokenTimestamp = $request->date_timestamp;

        if (!$tokenTimestamp) {
            return ResponseFormatter::error('Token absensi tidak valid. Silakan muat ulang halaman dan coba lagi.', 400);
        }

        $nowTimestamp = now()->timestamp;
        if (abs($nowTimestamp - intval($tokenTimestamp)) > 300) {
            return ResponseFormatter::error('Absensi sudah kedaluwarsa. Silakan muat ulang halaman dan coba lagi.', 400);
        }

        if ($request->latitude && $request->longitude) {
            $absenceArea = AbsenceArea::where('branch_id', $employee->branch_id)->first();
        }
    }
}
