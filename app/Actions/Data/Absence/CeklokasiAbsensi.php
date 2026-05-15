<?php

namespace App\Actions\Data\Absence;

class CeklokasiAbsensi
{
    public function execute($absenceArea, $request)
    {
        $earthRadius = 6371000; // meters
        $latFrom = deg2rad($absenceArea->latitude);
        $lonFrom = deg2rad($absenceArea->longitude);
        $latTo = deg2rad($request->latitude);
        $lonTo = deg2rad($request->longitude);

        $latDelta = $latTo - $latFrom;
        $lonDelta = $lonTo - $lonFrom;

        $a = sin($latDelta / 2) * sin($latDelta / 2) +
            cos($latFrom) * cos($latTo) *
            sin($lonDelta / 2) * sin($lonDelta / 2);
        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
        $distance = $earthRadius * $c;

        $allowedRadius = 200;

        if ($distance > $allowedRadius) {
            return false;
        }

        return true;
    }
}
