<?php

namespace App\Actions\Data\Absence;

use App\Actions\Data\Absence\CeklokasiAbsensi;
use App\Models\AbsenceArea;
use App\Services\PusherService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CheckAttendanceLocation
{
    // Constants for attendance types
    private const ATTENDANCE_TYPE_REJECTED = 4;
    private const STATUS_REJECTED = 'DITOLAK';
    private const TIMEZONE = 'Asia/Makassar';

    /**
     * Check if location-based attendance is enabled and validate location
     */
    public function execute($employee, Request $request)
    {
        // Check if radius feature is enabled for the branch
        $checkRadius = AbsenceArea::where('branch_id', $employee->branch_id)->exists();

        if (!$checkRadius) {
            return $this->createLocationDisabledResponse($employee);
        }

        // Validate location if provided
        if ($request->has(['latitude', 'longitude'])) {
            $absenceArea = AbsenceArea::where('branch_id', $employee->branch_id)->first();

            if ($absenceArea && $absenceArea->latitude && $absenceArea->longitude) {
                $cekLokasiAbsensi = app(CeklokasiAbsensi::class)->execute($absenceArea, $request);

                if (!$cekLokasiAbsensi) {
                    return $this->createLocationOutOfRangeResponse($employee);
                }
            }
        }

        return null;
    }

    /**
     * Create response when location feature is disabled
     */
    private function createLocationDisabledResponse($employee)
    {
        return [
            'message' => 'Fitur absensi dengan radius lokasi belum diaktifkan. Silakan hubungi atasan atau admin untuk konfirmasi.',
            'type' => 4,
            'data' => [
                'employee' => $employee,
                'type' => 4,
                'status' => self::STATUS_REJECTED,
                'dateTime' => now(self::TIMEZONE)->format('Y-m-d H:i:s'),
            ]
        ];
    }

    /**
     * Create response when employee is out of allowed location range
     */
    private function createLocationOutOfRangeResponse($employee)
    {
        return [
            'message' => 'Anda berada di luar radius area absensi yang diizinkan.',
            'type' => 4,
            'data' => [
                'employee' => $employee->toArray(),
                'type' => 4,
                'status' => self::STATUS_REJECTED,
                'dateTime' => now(self::TIMEZONE)->format('Y-m-d H:i:s'),
            ]
        ];
    }
}
