<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EmployeePerformanceResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
            $month = $request->month ?? now()->month;
            $year = $request->year ?? now()->year;

            $performances = $this->performances->filter(function($p) use ($month, $year) {
                return $p->month == $month && $p->year == $year;
            });

            $valAttendance = $this->calculateScore($performances, 'attendance', 0.25);
            $valChecklist = $this->calculateScore($performances, 'checklist', 0.20);
            $valProductivity = $this->calculateScore($performances, 'productivity', 0.25);
            $valAttitude = $this->calculateScore($performances, 'attitude', 0.15);
            $valIntitiative = $this->calculateScore($performances, 'initiative', 0.15);

            $total = $valAttendance + $valChecklist + $valProductivity + $valAttitude + $valIntitiative;

            return [
                'id' => $this->id,
                'employee' => $this->name,
                'cabang' => $this->branch?->name ?? '-',
                'departemen' => $this->department?->name ?? '-',
                'jabatan' => $this->position?->name ?? '-',
                'image_url' => $this->image ? url('storage/' . $this->image) : null,
                'period' => [
                    'month' => (int)$month,
                    'year' => (int)$year
                ],
                'attendance' => $valAttendance,
                'checklist' => $valChecklist,
                'productivity' => $valProductivity,
                'attitude' => $valAttitude,
                'initiative' => $valIntitiative,
                'total_score' => round($total, 1),

                'meta_config' =>[
                    'editable_categories' => ['productivity','attitude','initiative'],
                    'readonly_categories' => ['attendance','checklist']
                ]
            ];
    }

    protected function calculateScore($performances, $categoryDbName, $weight)
    {
        $record = $performances->firstWhere('category', $categoryDbName);
        $scoreData = $record?->score;

        if (empty($scoreData)) return 0;
        if (is_array($categoryDbName,['attendance','checklist'])){
            $val = 0;
            if(is_array($scoreData)){
                $val = $scoreData['score'];
            }

            elseif(is_numeric($scoreData)){
                $val = $scoreData;
            }
            return round($val * $weight, 1);
        }
        if (is_array($scoreData)){
            $validScores = array_filter($scoreData, fn($v) => $v !== null && $v !== '');
            $count =count($validScores);

            if ($count === 0) return 0;
            $totalRaw = array_sum ($validScores);
            // Formula untuk skala 1-5: (rata-rata × 2) × bobot
            // Contoh: jika semua indikator = 5, maka (5 × 2) × 0.25 = 2.5
            return round((($totalRaw / $count) * 2) * $weight, 1);
        }
        
        return 0;
    }
}