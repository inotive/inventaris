<?php

namespace App\Actions\Data\Absence;

use App\Models\Attendance;
use App\Models\EmployeeDataset;
use App\Services\PusherService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class AbsensiCheckOut
{
    // Constants for attendance types
    private const ATTENDANCE_TYPE_CHECKOUT = 5;
    private const ATTENDANCE_TYPE_EARLY_CHECKOUT = 6;
    private const ATTENDANCE_TYPE_REJECTED = 4;
    private const ATTENDANCE_TYPE_OVERTIME = 1;

    // Constants for attendance status
    private const STATUS_CHECKOUT = Attendance::STATUS_COMPLETE;
    private const STATUS_REJECTED = 'DITOLAK';

    // Constants for timezone and timing
    private const TIMEZONE = 'Asia/Makassar';
    private const MINIMUM_WORK_DURATION_MINUTES = 15;

    // Date formats for parsing tolerance
    private const TOLERANCE_FORMATS = ['Y-m-d H:i:s', 'Y-m-d H:i', 'H:i:s', 'H:i'];

    /**
     * Execute check-out process
     */
    public function execute($employee, $attendance, string $currentTime, string $dateTime, ?string $photoPath = null, $isBarcode = false, $isFaceCorrect = null): JsonResponse
    {
        Log::info('🟢 [CHECK-OUT] Memulai proses check-out', [
            'employee_id' => $employee->id,
            'employee_name' => $employee->name,
            'attendance_id' => $attendance->id,
            'current_time' => $currentTime,
            'jam_masuk' => $attendance->jam_masuk,
            'is_barcode' => $isBarcode,
        ]);

        $attendanceType = 1;
        $attendanceStatus = self::STATUS_CHECKOUT;
        $keterangan = 'Absensi pulang tepat waktu';
        $message = 'Absensi pulang berhasil. Semoga hari Anda menyenangkan!';
        $overtimeMinute = null;

        // Check if at least 15 minutes have passed since check-in
        if ($attendance->jam_masuk) {
            $jamMasuk = Carbon::createFromFormat('H:i:s', $attendance->jam_masuk, self::TIMEZONE);
            $current = Carbon::createFromFormat('H:i:s', $currentTime, self::TIMEZONE);
            $minLeave = $jamMasuk->copy()->addMinutes(self::MINIMUM_WORK_DURATION_MINUTES);

            if ($current->lt($minLeave)) {
                Log::warning('⚠️ [CHECK-OUT] Checkout terlalu cepat', [
                    'employee_id' => $employee->id,
                    'jam_masuk' => $attendance->jam_masuk,
                    'current_time' => $currentTime,
                    'minimum_leave_time' => $minLeave->format('H:i:s'),
                ]);
                return $this->createTooEarlyCheckoutResponse($employee, $dateTime, $isBarcode);
            }
        }

        // Handle overtime and early checkout logic
        $shift = $attendance->shift;
        $now = now(self::TIMEZONE);
        $currentCarbon = $this->createCurrentDateTime($currentTime, $now);

        if ($shift) {
            // Parse overtime start time with error handling
            $overtimeStartCarbon = $this->parseTime($shift->overtime_start, $now);
            $hasOvertime = $overtimeStartCarbon !== null;

            if ($hasOvertime) {
                if ($currentCarbon->gt($overtimeStartCarbon)) {
                    // Pulang setelah waktu lembur
                    $attendanceStatus = Attendance::STATUS_COMPLETE;
                    $attendanceType = 1;
                    $keterangan = 'Absensi pulang lebih dari waktu lembur';
                    $overtimeMinute = (int) floor($overtimeStartCarbon->diffInMinutes($currentCarbon, false));

                    Log::info('⏰ [CHECK-OUT] Overtime terdeteksi', [
                        'employee_id' => $employee->id,
                        'overtime_minutes' => $overtimeMinute,
                        'overtime_start' => $overtimeStartCarbon->format('H:i:s'),
                        'checkout_time' => $currentCarbon->format('H:i:s'),
                    ]);
                    // dd($overtimeMinute);
                } else {
                    // Parse end time for early checkout check
                    $endTimeCarbon = $this->parseTime($shift->end_time, $now);
                    if ($endTimeCarbon && $currentCarbon->lt($endTimeCarbon)) {
                        // Pulang sebelum jam selesai (early checkout)
                        $attendanceStatus = Attendance::STATUS_COMPLETE;
                        $attendanceType = 1;
                        $keterangan = 'Pulang lebih awal dari jadwal';

                        Log::info('🏃 [CHECK-OUT] Early checkout terdeteksi', [
                            'employee_id' => $employee->id,
                            'shift_end' => $endTimeCarbon->format('H:i:s'),
                            'checkout_time' => $currentCarbon->format('H:i:s'),
                        ]);
                    }
                }
            } else {
                // Tidak ada overtime, cek early checkout
                $endTimeCarbon = $this->parseTime($shift->end_time, $now);
                if ($endTimeCarbon && $currentCarbon->lt($endTimeCarbon)) {
                    $attendanceStatus = Attendance::STATUS_COMPLETE;
                    $attendanceType = 1;
                    $keterangan = 'Pulang lebih awal dari jadwal';
                }
            }
        }

        // Update attendance record
        $this->updateAttendanceRecord($attendance, $currentTime, $attendanceStatus, $keterangan, $photoPath, $overtimeMinute, $isFaceCorrect);


        return $this->createCheckoutResponse($employee, $attendanceType, $attendanceStatus, $dateTime, $isBarcode);
    }

    /**
     * Create response when employee tries to checkout too early
     */
    private function createTooEarlyCheckoutResponse($employee, string $dateTime, $isBarcode = false): JsonResponse
    {
        if ($isBarcode) {
            app(PusherService::class)->pusherAttendanceResponse(
                $employee,
                4,
                'CHECKOUT DITOLAK',
                $dateTime,
                'Anda belum dapat melakukan absensi pulang. Silakan coba lagi nanti.',
            );
        }

        return response()->json([
            'message' => 'Anda belum dapat melakukan absensi pulang. Silakan coba lagi nanti.',
            'data' => [
                'employee' => $employee,
                'type' => 4,
                'status' => 'CHECKOUT DITOLAK',
                'dateTime' => $dateTime,
            ]
        ], 200);
    }

    /**
     * Parse time from various formats with error handling
     */
    private function parseTime($timeRaw, Carbon $now): ?Carbon
    {
        $timeString = trim((string) $timeRaw);

        if (empty($timeString)) {
            return null;
        }

        foreach (self::TOLERANCE_FORMATS as $format) {
            try {
                $timeDateTime = Carbon::createFromFormat($format, $timeString, self::TIMEZONE);
                // Normalize to today's date for comparison
                $timeDateTime->setDate($now->year, $now->month, $now->day);
                return $timeDateTime;
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
     * Update attendance record with checkout information
     */
    private function updateAttendanceRecord($attendance, string $currentTime, string $status, string $keterangan, ?string $photoPath, ?string $overtimeMinute = null, $isFaceCorrect = null): void
    {
        Log::info('💾 [CHECK-OUT] Update record absensi', [
            'attendance_id' => $attendance->id,
            'employee_id' => $attendance->employee_id,
            'jam_keluar' => $currentTime,
            'status' => $status,
            'keterangan' => $keterangan,
            'has_photo' => $photoPath ? 'yes' : 'no',
            'overtime_minutes' => $overtimeMinute,
        ]);

        $attendance->jam_keluar = $currentTime;
        $attendance->status = $status;
        $attendance->keterangan = $keterangan;

        if ($photoPath) {
            $attendance->foto_keluar = $photoPath;

            // Save to employee_datasets as well
            EmployeeDataset::create([
                'employee_id' => $attendance->employee_id,
                'image_path' => $photoPath,
            ]);
        }

        if ($overtimeMinute) {
            $attendance->overtime_duration_minutes = $overtimeMinute;
        }

        // Simpan is_face_correct jika dikirim dari request
        if ($isFaceCorrect !== null) {
            if ($isFaceCorrect) {
                // Wajah salah saat check-out: upgrade 1→2 jika check-in juga salah
                $attendance->is_face_correct = ($attendance->is_face_correct == 1) ? 2 : 1;
            }
            // Jika wajah benar (0), tidak menimpa nilai check-in
        }

        $attendance->save();

        Log::info('✅ [CHECK-OUT] Absensi berhasil diupdate', [
            'attendance_id' => $attendance->id,
            'status' => $attendance->status,
            'jam_masuk' => $attendance->jam_masuk,
            'jam_keluar' => $attendance->jam_keluar,
        ]);
    }

    /**
     * Create checkout response
     */
    private function createCheckoutResponse($employee, int $type, string $status, string $dateTime, $isBarcode = false): JsonResponse
    {
        if ($isBarcode) {
            app(PusherService::class)->pusherAttendanceResponse(
                $employee,
                1,
                $status,
                $dateTime,
                'Absensi pulang berhasil.',
            );
        }

        return response()->json([
            'message' => 'Absensi pulang berhasil.',
            'type' => 1,
            'data' => [
                'employee' => $employee,
                'type' => 1,
                'status' => $status,
                'dateTime' => $dateTime,
            ]
        ], 200);
    }
}
