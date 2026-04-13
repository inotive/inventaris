<?php

namespace App\Actions\Data\Absence;

use App\Models\Attendance;
use App\Services\PusherService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;

class CheckDuplicateAttendance
{
    // Constants for attendance types
    private const ATTENDANCE_TYPE_ALREADY_ATTENDED = 3;
    private const STATUS_ALREADY_ATTENDED = 'SUDAH ABSEN';
    private const TIMEZONE = 'Asia/Makassar';
    private const DUPLICATE_THRESHOLD_MINUTES = 240;

    /**
     * Check if employee has already attended within the threshold time
     */
    public function execute($employee, Carbon $now, string $dateTime)
    {
        $lastAttendance = Attendance::where('employee_id', $employee->id)
            ->whereDate('tanggal', $now->format('Y-m-d'))
            ->orderByDesc('updated_at')
            ->first();

        if (!$lastAttendance || !$lastAttendance->jam_masuk) {
            return null;
        }

        $lastAttendanceDateTime = Carbon::createFromFormat(
            'Y-m-d H:i:s',
            $now->format('Y-m-d') . ' ' . $lastAttendance->jam_masuk,
            self::TIMEZONE
        );

        $timeDifferenceInMinutes = $lastAttendanceDateTime->diffInMinutes($now);

        if ($timeDifferenceInMinutes <= self::DUPLICATE_THRESHOLD_MINUTES) {
            $lastAttendanceTime = Carbon::createFromFormat('H:i:s', $lastAttendance->jam_masuk, self::TIMEZONE);

            return $this->createAlreadyAttendedResponse(
                $employee,
                $dateTime,
                $lastAttendanceTime->format('H:i'),
            );
        }

        return null;
    }

    /**
     * Create response for already attended employee
     */
    private function createAlreadyAttendedResponse($employee, string $dateTime, string $lastAttendanceTime)
    {
        app(PusherService::class)->pusherAttendanceResponse(
            $employee,
            self::ATTENDANCE_TYPE_ALREADY_ATTENDED,
            self::STATUS_ALREADY_ATTENDED,
            $dateTime,
            "Anda sudah absen pada {$lastAttendanceTime}. Silakan cek history absensi terakhir Anda.",
        );
        return [
            'message' => "Anda sudah absen pada {$lastAttendanceTime}. Silakan cek history absensi terakhir Anda.",
            'sound_type' => 3,
            'data' => [
                'employee' => $employee,
                'type' => self::ATTENDANCE_TYPE_ALREADY_ATTENDED,
                'status' => self::STATUS_ALREADY_ATTENDED,
                'dateTime' => $dateTime,
            ]
        ];
    }
}
