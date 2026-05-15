<?php

namespace App\Actions\Data\Dashboard;

use App\Models\EmployeePerformance;
use Carbon\Carbon;

class GetTopPerformers
{
    public function execute($limit = 3, $branchId = null, $departmentId = null)
    {
        $currentYear = Carbon::now()->year;

        // Fetch performance records for current year
        $query = EmployeePerformance::with('employee')
            ->where('year', $currentYear);

        if ($branchId) {
            $query->whereHas('employee', function ($q) use ($branchId) {
                $q->where('branch_id', $branchId);
            });
        }

        if ($departmentId) {
            $query->whereHas('employee', function ($q) use ($departmentId) {
                $q->where('department_id', $departmentId);
            });
        }

        $performances = $query->get();

        $grouped = $performances->groupBy('employee_id');


        $employees = $grouped->map(function ($recs) {
            $employee = $recs->first()->employee;
            if (!$employee) return null;

            $totalScore = 0;
            $count = 0;

            foreach ($recs as $rec) {
                $rawScore = $rec->score;

                if (is_array($rawScore)) {
                    foreach ($rawScore as $val) {
                        if (is_numeric($val)) {
                            $totalScore += (float)$val;
                            $count++;
                        }
                    }
                }
            }

            $avgScore = $count > 0 ? round($totalScore / $count, 1) : 0;

            return [
                'id' => $employee->id,
                'name' => $employee->name,
                'department' => $employee->department->name ?? '-',
                'score' => $avgScore,
                'display_score' => $avgScore . '/100',
            ];
        })
            ->filter(function ($e) {
                return $e && $e['score'] > 0;
            })
            ->sortByDesc('score')
            ->take($limit)
            ->values();

        return $employees->toArray();
    }
}
