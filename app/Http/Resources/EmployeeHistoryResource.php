<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EmployeeHistoryResource extends JsonResource
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

       $filteredPerformances = $this->performances->filter(function ($performance) {
            $category = $performance->category;
            $scoreData = $performance->score; 

            if (in_array($category, ['Kehadiran', 'Kuantitas', 'Checklist'])) {
                $val = is_array($scoreData) ? ($scoreData['score'] ?? 0) : 0;
                return $val > 0;
            }

            if (in_array($category, ['Keterampilan', 'Kerjasama', 'Disiplin'])) {
                return true;
            }

            return true; 
        });

        $assessedCategories =  $filteredPerformances->pluck('category')->unique()->values();
        $lastUpdateRecord = $filteredPerformances->sortByDesc('updated_at')->first();
        $lastUpdateDate = $lastUpdateRecord ? $lastUpdateRecord->updated_at->format('Y-m-d H:i:s') : null;

        return [
            'id' => $this->id,
            'employee_id' => $this->id,
            'employee_name' => $this->name,
            'branch' => $this->branch?->name ?? '-',
            'department' => $this->department?->name ?? '-',
            'position' => $this->position?->name ?? '-',
            'category' => $assessedCategories,
            'month' => (int)$month,
            'year' => (int)$year,
            'updated_at' => $lastUpdateDate,
        ];
    }
}
