<?php

namespace App\Http\Controllers;

use App\Actions\Data\Absence\AbsensiChekIn;
use App\Actions\Data\Absence\AbsensiCheckOut;
use App\Actions\Data\Absence\CheckAttendanceLocation;
use App\Actions\Data\Absence\CheckDuplicateAttendance;
use App\Actions\Data\Absence\HandlePhotoUpload;
use App\Http\Requests\Presence\PresenceRequest;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Employee;
use App\Services\PusherService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class PresenceController extends Controller
{
    public function index()
    {
        $listEmployeeData = Employee::select([
            'id',
            'name',
            'path',
            'file_name',
        ])
            ->whereNotNull('path')
            ->whereNotNull('file_name')
            ->get()
            ->map(function ($employee) {
                return [
                    'id' => $employee->id,
                    'name' => $employee->name,
                    'photo_url' => $employee->photo_url,
                    'label' => $employee->id . '|' . $employee->name,
                ];
            });


        return Inertia::render('Admin/Presence/Index', [
            'employees' => $listEmployeeData,
            'branchId' => Auth::user()->employee->branch_id,
        ]);
    }

    public function getInformationEmployee(Request $request)
    {
        $employee = Employee::find($request->employee_id);

        if (!$employee) {
            return response()->json(['message' => 'Employee not found'], 404);
        }
        return response()->json([
            'id' => $employee->id,
            'name' => $employee->name,
            'photo_url' => $employee->photo_url,
        ]);
    }

    public function attendaceEmployee(Request $request): \Illuminate\Http\JsonResponse
    {
        // Get employee with required relationships
        $employee = Employee::with([
            'shift',
            'department:id,name',
            'branch:id,name',
            'position:id,name',
        ])->find($request->employee_id);

        if (!$employee) {
            return response()->json(['message' => 'Employee not found'], 404);
        }

        // Set timezone to ensure consistency
        $now = now('Asia/Makassar');
        $dateTime = $now->format('Y-m-d H:i:s');
        $currentTime = $now->format('H:i:s');

        // // Check location-based attendance restrictions
        // $locationCheck = app(CheckAttendanceLocation::class)->execute($employee, $request);
        // if ($locationCheck) {
        //     return response()->json($locationCheck, 200);
        // }

        // // Check for duplicate attendance within threshold time
        $duplicateCheck = app(CheckDuplicateAttendance::class)->execute($employee, $now, $dateTime);

        if ($duplicateCheck) {
            return response()->json($duplicateCheck, 200);
        }

        // Get existing attendance record for today
        $existingAttendance = $employee->attendances()
            ->whereDate('tanggal', $now->format('Y-m-d'))
            ->first();

        // Handle check-in process
        if (!$existingAttendance || !$existingAttendance->jam_masuk) {
            return app(AbsensiChekIn::class)->execute($employee, $dateTime, $request, $existingAttendance);
        }

        // Handle check-out process, only if at least 2 hours have passed since check-in
        if ($existingAttendance->jam_masuk && !$existingAttendance->jam_keluar) {
            // Cek selisih waktu antara jam_masuk dan sekarang
            $jamMasuk = \Carbon\Carbon::createFromFormat('H:i:s', $existingAttendance->jam_masuk, 'Asia/Makassar');
            // Allow checking out >=2 hours after check-in
            if ($jamMasuk->diffInMinutes($now) < 120) {
                return $this->attendanceResponse(
                    $employee,
                    5,
                    'CHECKOUT DITOLAK',
                    $dateTime,
                    'Anda hanya dapat melakukan check out minimal 2 jam setelah check in.',
                    400
                );
            }

            $photoPath = app(HandlePhotoUpload::class)->execute($request, $employee);
            $isFaceCorrect = $request->has('is_face_correct') ? ($request->boolean('is_face_correct') ? 1 : 0) : null;
            return app(AbsensiCheckOut::class)->execute($employee, $existingAttendance, $currentTime, $dateTime, $photoPath, false, $isFaceCorrect);
        }

        // Employee has already completed both check-in and check-out for today
        return $this->attendanceResponse(
            $employee,
            5,
            'Absen Sudah Lengkap',
            $dateTime,
            'Anda telah melakukan absensi sebelumnya. Tidak perlu melakukan absensi ulang.',
            200
        );
    }

    public function attendaceEmployeeBarcode(PresenceRequest $request)
    {
        Log::info('[Barcode] Request received', [
            'user_id' => Auth::id(),
            'qr_token' => $request->qr_token,
            'ip' => $request->ip(),
        ]);

        // Cek apakah qr_token ada pada pada cache
        if (!$request->has('qr_token') || empty($request->qr_token)) {
            Log::warning('[Barcode] QR token kosong atau tidak ada');
            return response()->json([
                'message' => 'QR code tidak valid',
            ], 400);
        }

        // Simpan token ke cache (selama 15 menit)
        try {
            if (cache()->has('qr_token_' . $request->qr_token)) {
                Log::warning('[Barcode] QR token sudah digunakan atau kadaluarsa', ['qr_token' => $request->qr_token]);
                return response()->json([
                    'message' => 'QR code tidak dikenali atau sudah kadaluarsa',
                ], 400);
            }

            cache()->put('qr_token_' . $request->qr_token, true, now()->addMinutes(10));
            Log::info('[Barcode] QR token disimpan ke cache', ['qr_token' => $request->qr_token]);
        } catch (\Throwable $e) {
            Log::error('[Barcode] Gagal menyimpan QR token ke cache', ['error' => $e->getMessage()]);
        }

        // Get employee with required relationships
        $employee = Employee::with([
            'shift',
            'department:id,name',
            'branch:id,name',
            'position:id,name',
        ])->find(Auth::user()->employee->id);

        if (!$employee) {
            Log::error('[Barcode] Employee tidak ditemukan', ['user_id' => Auth::id()]);
            return response()->json(['message' => 'Employee not found'], 404);
        }

        Log::info('[Barcode] Employee ditemukan', [
            'employee_id' => $employee->id,
            'employee_name' => $employee->name,
        ]);

        // Set timezone to ensure consistency
        $now = now('Asia/Makassar');
        $dateTime = $now->format('Y-m-d H:i:s');
        $currentTime = $now->format('H:i:s');

        Log::info('[Barcode] Waktu saat ini', ['dateTime' => $dateTime, 'currentTime' => $currentTime]);

        // Check location-based attendance restrictions
        $locationCheck = app(CheckAttendanceLocation::class)->execute($employee, $request);
        if ($locationCheck) {
            Log::warning('[Barcode] Lokasi tidak valid', [
                'employee_id' => $employee->id,
                'message' => $locationCheck['message'] ?? '-',
            ]);
            app(PusherService::class)->pusherAttendanceResponse(
                $employee,
                $locationCheck['type'],
                $locationCheck['data']['status'],
                $dateTime,
                $locationCheck['message'],
            );

            return response()->json($locationCheck, 400);
        }

        // Get existing attendance record for today
        $existingAttendance = $employee->attendances()
            ->whereDate('tanggal', $now->format('Y-m-d'))
            ->first();

        Log::info('[Barcode] Status absensi hari ini', [
            'employee_id' => $employee->id,
            'existing_attendance_id' => $existingAttendance?->id,
            'jam_masuk' => $existingAttendance?->jam_masuk,
            'jam_keluar' => $existingAttendance?->jam_keluar,
        ]);

        // Handle check-in process
        if (!$existingAttendance || !$existingAttendance->jam_masuk) {
            Log::info('[Barcode] Proses CHECK-IN', ['employee_id' => $employee->id]);
            $response = app(AbsensiChekIn::class)->execute($employee, $dateTime, $request, $existingAttendance, true);

            return $response;
        }
        $jamMasuk = $existingAttendance->jam_masuk;

        // Handle check-out process with 2 hour minimum after check-in
        if ($existingAttendance->jam_masuk && !$existingAttendance->jam_keluar) {
            // Calculate the difference between current time and check-in time
            $jamMasuk = \Carbon\Carbon::createFromFormat('H:i:s', $existingAttendance->jam_masuk, 'Asia/Makassar');
            $currentJam = \Carbon\Carbon::createFromFormat('H:i:s', $currentTime, 'Asia/Makassar');
            $diffInMinutes = $jamMasuk->diffInMinutes($currentJam, false);

            Log::info('[Barcode] Cek syarat CHECK-OUT', [
                'employee_id' => $employee->id,
                'jam_masuk' => $existingAttendance->jam_masuk,
                'current_time' => $currentTime,
                'diff_minutes' => $diffInMinutes,
            ]);

            if ($diffInMinutes < 120) {
                Log::warning('[Barcode] CHECK-OUT ditolak — belum 2 jam', [
                    'employee_id' => $employee->id,
                    'diff_minutes' => $diffInMinutes,
                ]);
                app(PusherService::class)->pusherAttendanceResponse(
                    $employee,
                    4,
                    'CHECKOUT DITOLAK',
                    $dateTime,
                    'Anda hanya dapat melakukan check out minimal 2 jam setelah check in.',
                );
                return response()->json([
                    'message' => 'Anda hanya dapat melakukan check out minimal 2 jam setelah check in.',
                    'type' => 5,
                    'data' => [
                        'employee' => $employee,
                        'type' => 5,
                        'status' => 'CHECKOUT DITOLAK',
                        'dateTime' => $dateTime,
                    ]
                ], 400);
            }

            Log::info('[Barcode] Proses CHECK-OUT', ['employee_id' => $employee->id]);
            $photoPath = app(HandlePhotoUpload::class)->execute($request, $employee);
            $isFaceCorrect = $request->has('is_face_correct') ? ($request->boolean('is_face_correct') ? 1 : 0) : null;
            return app(AbsensiCheckOut::class)->execute($employee, $existingAttendance, $currentTime, $dateTime, $photoPath, true, $isFaceCorrect);
        }

        Log::info('[Barcode] Absensi sudah lengkap hari ini', ['employee_id' => $employee->id]);
        app(PusherService::class)->pusherAttendanceResponse(
            $employee,
            3,
            'SUDAH ABSEN',
            $dateTime,
            'Anda Sudah Melakukan Absensi',
        );

        return response()->json([
            'message' => 'Anda Sudah Melakukan Absensi',
            'data' => [
                'employee' => $employee,
                'type' => 3,
                'status' => 'SUDAH ABSEN',
                'dateTime' => $dateTime,
            ]
        ], 200);
    }

    /**
     * @param mixed $employee
     * @param string $type
     * @param string $status
     * @param string $dateTime
     * @param string $message
     * @param int $statusCode
     * @return \Illuminate\Http\JsonResponse
     */

    private function attendanceResponse($employee, $type, $status, $dateTime, $message, $statusCode = 200): \Illuminate\Http\JsonResponse
    {
        return response()->json([
            'message' => $message,
            'type' => $type,
            'data' => [
                'employee' => $employee,
                'type' => $type,
                'status' => $status,
                'dateTime' => $dateTime,
            ]
        ], $statusCode);
    }
}
