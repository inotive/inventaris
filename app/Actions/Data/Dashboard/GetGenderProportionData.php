<?php

namespace App\Actions\Data\Dashboard;

use App\Models\Employee;

class GetGenderProportionData
{
    public function execute()
    {
        // Get employee count by gender
        $genderData = Employee::selectRaw('gender, COUNT(*) as count')
            ->whereNotNull('gender')
            ->groupBy('gender')
            ->get()
            ->keyBy('gender');

        $maleCount = $genderData->get('Laki-laki', $genderData->get('Male', $genderData->get('L', collect(['count' => 0]))))['count'] ?? 0;
        $femaleCount = $genderData->get('Perempuan', $genderData->get('Female', $genderData->get('P', collect(['count' => 0]))))['count'] ?? 0;

        // If no data, return default values
        if ($maleCount == 0 && $femaleCount == 0) {
            return [50, 30];
        }

        return [$maleCount, $femaleCount];
    }
}
