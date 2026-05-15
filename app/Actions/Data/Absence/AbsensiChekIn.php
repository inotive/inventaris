<?php

namespace App\Actions\Data\Absence;

use App\Models\Attendance;
use App\Models\EmployeeDataset;
use App\Services\PusherService;
use App\Services\LivenessDetectionService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class AbsensiChekIn
{
    // Constants for attendance types
    private const ATTENDANCE_TYPE_ON_TIME = 1;
    private const ATTENDANCE_TYPE_LATE = 2;
    private const ATTENDANCE_TYPE_REJECTED = 4;

    // Constants for attendance status
    private const STATUS_ON_TIME = Attendance::STATUS_RUNNING;
    private const STATUS_LATE = 'TERLAMBAT';
    private const STATUS_REJECTED = 'DITOLAK';

    // Constants for timezone and storage
    private const TIMEZONE = 'Asia/Makassar';
    private const STORAGE_DISK = 'public';
    private const PHOTO_STORAGE_PATH = 'attendances';

    // Date formats for parsing tolerance
    private const TOLERANCE_FORMATS = ['Y-m-d H:i:s', 'Y-m-d H:i', 'H:i:s', 'H:i'];

    /**
     * Execute attendance check-in process
     * Note: Liveness detection is now handled in the frontend before this method is called
     */
    public function execute($employee, $dateTime, Request $request, $checkAlreadyAttendance, $isBarcode = false): JsonResponse
    {
        $now = now(self::TIMEZONE);
        $currentTime = $now->format('H:i:s');

        Log::info('🔵 [CHECK-IN] Memulai proses check-in', [
            'employee_id' => $employee->id,
            'employee_name' => $employee->name,
            'current_time' => $currentTime,
            'date' => $now->format('Y-m-d'),
            'is_barcode' => $isBarcode,
            'has_existing_attendance' => $checkAlreadyAttendance ? 'yes' : 'no',
        ]);

        // Get employee's shift for today
        $shiftWork = $this->getEmployeeShiftWork($employee, $now);
        if (!$shiftWork) {
            Log::warning('⚠️ [CHECK-IN] Shift tidak ditemukan', [
                'employee_id' => $employee->id,
                'employee_name' => $employee->name,
                'date' => $now->format('Y-m-d'),
            ]);
            return $this->createRejectedResponse($employee, $dateTime, 'Anda tidak memiliki jadwal shift untuk hari ini. Silakan hubungi atasan atau admin.', 2, $isBarcode);
        }

        $shift = $shiftWork->shift;

        Log::info('✅ [CHECK-IN] Shift ditemukan', [
            'employee_id' => $employee->id,
            'shift_id' => $shift->id,
            'shift_name' => $shift->name,
            'shift_start' => $shift->start_time,
            'shift_end' => $shift->end_time,
            'late_tolerance' => $shift->late_tolerance,
        ]);

        // Calculate attendance status and details
        $attendanceDetails = $this->calculateAttendanceStatus($shift, $currentTime, $now);

        if ($attendanceDetails['type'] === self::ATTENDANCE_TYPE_REJECTED) {
            Log::warning('🚫 [CHECK-IN] Absensi ditolak', [
                'employee_id' => $employee->id,
                'reason' => $attendanceDetails['message'],
                'late_minutes' => $attendanceDetails['late_minutes'],
            ]);
            return $this->createRejectedResponse($employee, $dateTime, $attendanceDetails['message'], $attendanceDetails['type'], $isBarcode);
        }

        // Handle photo upload
        $photoPath = $this->handlePhotoUpload($request);

        Log::info('📸 [CHECK-IN] Status foto', [
            'employee_id' => $employee->id,
            'has_photo' => $photoPath ? 'yes' : 'no',
            'photo_path' => $photoPath,
        ]);

        // Save or update attendance record
        $this->saveAttendanceRecord($employee, $shift, $now, $currentTime, $attendanceDetails, $photoPath, $checkAlreadyAttendance, $shiftWork->id, $isBarcode, $request);

        return $this->createAttendanceResponse($employee, $attendanceDetails, $dateTime, $isBarcode);
    }

    /**
     * Get employee's shift work for today
     */
    private function getEmployeeShiftWork($employee, Carbon $now)
    {
        return $employee->attendanceShiftWorks()
            ->whereDate('work_date', $now->format('Y-m-d'))
            ->with('shift')
            ->first();
    }

    /**
     * Calculate attendance status based on shift and current time
     */
    private function calculateAttendanceStatus($shift, string $currentTime, Carbon $now): array
    {
        $attendanceType = self::ATTENDANCE_TYPE_ON_TIME;
        $attendanceStatus = self::STATUS_ON_TIME;
        $message = 'Anda berhasil absen tepat waktu. Terima kasih atas kedisiplinan Anda.';
        $keterangan = 'Absensi masuk tepat waktu';
        $lateMinutes = 0.0;

        if ($shift && $shift->late_tolerance) {
            $toleranceDateTime = $this->parseToleranceTime($shift->late_tolerance, $now);
            $startDateTime = $this->parseToleranceTime($shift->start_time, $now);

            if ($toleranceDateTime) {
                $currentDateTime = $this->createCurrentDateTime($currentTime, $now);

                if ($currentDateTime->gt($toleranceDateTime)) {
                    // Jika melebihi late_toleransi maka absen ditolak
                    $keterangan = 'Absensi masuk setelah batas toleransi keterlambatan';
                    $lateMinutes = round($toleranceDateTime->diffInSeconds($currentDateTime) / 60, 2);
                    $message = 'Absensi berhasil, namun Anda terlambat melebihi batas toleransi.';

                    Log::info('⏰ [CHECK-IN] Keterlambatan terdeteksi', [
                        'late_minutes' => $lateMinutes,
                        'tolerance_time' => $toleranceDateTime->format('H:i:s'),
                        'actual_time' => $currentDateTime->format('H:i:s'),
                    ]);
                }
            }
        }

        return [
            'type' => $attendanceType,
            'status' => $attendanceStatus,
            'message' => $message,
            'keterangan' => $keterangan,
            'late_minutes' => $lateMinutes
        ];
    }

    /**
     * Parse tolerance time from various formats
     */
    private function parseToleranceTime($toleranceRaw, Carbon $now): ?Carbon
    {
        $toleranceString = trim((string) $toleranceRaw);

        foreach (self::TOLERANCE_FORMATS as $format) {
            try {
                $toleranceDateTime = Carbon::createFromFormat($format, $toleranceString, self::TIMEZONE);
                // Normalize to today's date for comparison
                $toleranceDateTime->setDate($now->year, $now->month, $now->day);
                return $toleranceDateTime;
            } catch (\Throwable $e) {
                continue;
            }
        }

        return null;
    }

    /**
     * Create current datetime for comparison
     */
    private function createCurrentDateTime(string $currentTime, Carbon $now): Carbon
    {
        return Carbon::createFromFormat('H:i:s', $currentTime, self::TIMEZONE)
            ->setDate($now->year, $now->month, $now->day);
    }

    /**
     * Handle photo upload from file or base64
     */
    private function handlePhotoUpload(Request $request): ?string
    {
        if ($request->hasFile('photo')) {
            return $request->file('photo')->store(self::PHOTO_STORAGE_PATH, self::STORAGE_DISK);
        }

        if ($request->filled('photo_base64')) {
            return $this->handleBase64Photo($request->input('photo_base64'));
        }

        return null;
    }

    /**
     * Handle base64 photo upload
     */
    private function handleBase64Photo(string $base64Data): ?string
    {
        if (!preg_match('/^data:image\/(\w+);base64,/', $base64Data, $matches)) {
            return null;
        }

        $extension = strtolower($matches[1]);
        $imageData = base64_decode(substr($base64Data, strpos($base64Data, ',') + 1));
        $fileName = self::PHOTO_STORAGE_PATH . '/' . uniqid('att_') . '.' . $extension;

        Storage::disk(self::STORAGE_DISK)->put($fileName, $imageData);

        return $fileName;
    }

    /**
     * Save or update attendance record
     */
    private function saveAttendanceRecord($employee, $shift, Carbon $now, string $currentTime, array $attendanceDetails, ?string $photoPath, $existingAttendance, $shiftWorkId, $isBarcode = false, ?Request $request = null): void
    {
        $attendanceData = [
            'jam_masuk' => $currentTime,
            'status' => $attendanceDetails['status'],
            'keterangan' => $attendanceDetails['keterangan'],
            'shift_id' => $shift->id,
            'shift_work_id' => $shiftWorkId,
        ];

        if ($photoPath) {
            $attendanceData['foto_masuk'] = $photoPath;

            // Save to employee_datasets as well
            EmployeeDataset::create([
                'employee_id' => $employee->id,
                'image_path' => $photoPath,
            ]);
        }

        $attendanceData['late_duration_minutes'] = (int) floor($attendanceDetails['late_minutes']);

        // Simpan is_face_correct jika dikirim dari request
        if ($request && $request->has('is_face_correct')) {
            $attendanceData['is_face_correct'] = $request->boolean('is_face_correct') ? 1 : 0;
        }

        if ($existingAttendance) {
            $this->updateExistingAttendance($existingAttendance, $attendanceData);
        } else {
            $this->createNewAttendance($employee, $shift, $now, $attendanceData);
        }
    }

    /**
     * Update existing attendance record
     */
    private function updateExistingAttendance($attendance, array $data): void
    {
        Log::info('🔄 [CHECK-IN] Update absensi existing', [
            'attendance_id' => $attendance->id,
            'employee_id' => $attendance->employee_id,
            'previous_status' => $attendance->status,
            'new_data' => $data,
        ]);

        foreach ($data as $key => $value) {
            $attendance->$key = $value;
        }
        $attendance->save();

        Log::info('✅ [CHECK-IN] Absensi berhasil diupdate', [
            'attendance_id' => $attendance->id,
            'status' => $attendance->status,
        ]);
    }

    /**
     * Create new attendance record
     */
    private function createNewAttendance($employee, $shift, Carbon $now, array $data): void
    {
        Log::info('➕ [CHECK-IN] Membuat absensi baru', [
            'employee_id' => $employee->id,
            'employee_name' => $employee->name,
            'department_id' => $employee->department_id,
            'date' => $now->format('Y-m-d'),
            'data' => $data,
        ]);

        $attendance = new Attendance();
        $attendance->employee_id = $employee->id;
        $attendance->department_id = $employee->department_id;
        $attendance->tanggal = $now->format('Y-m-d');

        foreach ($data as $key => $value) {
            $attendance->$key = $value;
        }

        $attendance->save();

        Log::info('✅ [CHECK-IN] Absensi baru berhasil disimpan', [
            'attendance_id' => $attendance->id,
            'employee_id' => $employee->id,
            'status' => $attendance->status,
            'jam_masuk' => $attendance->jam_masuk,
        ]);
    }

    /**
     * Create rejected attendance response
     */
    private function createRejectedResponse($employee, $dateTime, $message, $type, $isBarcode = false): JsonResponse
    {
        return $this->attendanceResponse(
            $employee,
            $type,
            self::STATUS_REJECTED,
            $dateTime,
            $message,
            200,
            $isBarcode
        );
    }

    /**
     * Create attendance response
     */
    private function createAttendanceResponse($employee, array $attendanceDetails, $dateTime, $isBarcode = false): JsonResponse
    {
        return $this->attendanceResponse(
            $employee,
            $attendanceDetails['type'],
            $attendanceDetails['status'],
            $dateTime,
            $attendanceDetails['message'],
            200,
            $isBarcode
        );
    }

    /**
     * Create standardized attendance response
     */
    private function attendanceResponse($employee, int $type, string $status, $dateTime, string $message, int $statusCode = 200, $isBarcode = false): JsonResponse
    {
        $status = $status == "RUNNING" ? 'Berhasil Absen' : $status;

        if ($isBarcode) {
            app(PusherService::class)->pusherAttendanceResponse(
                $employee,
                $type,
                $status,
                $dateTime,
                $message,
            );
        }

        return response()->json([
            'message' => $message,
            'data' => [
                'employee' => $employee,
                'type' => $type,
                'status' => $status,
                'dateTime' => $dateTime,
            ]
        ], $statusCode);
    }
}
