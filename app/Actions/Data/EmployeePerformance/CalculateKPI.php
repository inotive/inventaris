<?php

namespace App\Actions\Data\EmployeePerformance;

use App\Models\Employee;
use App\Models\EmployeePerformance;
use App\Models\Attendance;
use App\Models\EmployeeLeaveRequest;
use App\Models\Inspection;
use App\Models\Answer;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class CalculateKPI
{
    public function execute(int $month, int $year, ?int $branchId = null, ?int $userBranchId = null, bool $isSuperadmin = false): array
    {
        // Apply branch filter based on user role
        if (!$isSuperadmin && $userBranchId) {
            $branchId = $userBranchId;
        }

        // Get employees based on branch filter
        $employeesQuery = Employee::query();
        if ($branchId) {
            $employeesQuery->where('branch_id', $branchId);
        } elseif (!$isSuperadmin && $userBranchId) {
            $employeesQuery->where('branch_id', $userBranchId);
        }

        $employees = $employeesQuery->get();

        $monthStart = Carbon::create($year, $month, 1);
        $monthEnd = Carbon::create($year, $month, $monthStart->daysInMonth);
        $currentDate = now();

        // Limit to current date if month is current month
        if ($month == now()->month && $year == now()->year) {
            if ($monthEnd->gt($currentDate)) {
                $monthEnd = $currentDate->copy();
            }
        }

        $calculated = 0;
        $errors = [];

        DB::beginTransaction();
        try {
            foreach ($employees as $employee) {
                try {
                    $result = $this->calculateEmployeeKPI($employee, $month, $year, $monthStart, $monthEnd, $currentDate);

                    // Update or create Attendance KPI - store as JSON with score key
                    EmployeePerformance::updateOrCreate(
                        [
                            'employee_id' => $employee->id,
                            'category' => 'Kehadiran',
                            'month' => $month,
                            'year' => $year,
                        ],
                        [
                            'score' => ['score' => $result['attendance_score']],
                            'notes' => "Otomatis dihitung pada " . now()->format('Y-m-d H:i:s'),
                        ]
                    );

                    // Update or create Checklist KPI - store as JSON with score key
                    EmployeePerformance::updateOrCreate(
                        [
                            'employee_id' => $employee->id,
                            'category' => 'Kuantitas',
                            'month' => $month,
                            'year' => $year,
                        ],
                        [
                            'score' => ['score' => $result['checklist_score']],
                            'notes' => "Otomatis dihitung pada " . now()->format('Y-m-d H:i:s'),
                        ]
                    );

                    $calculated++;
                } catch (\Exception $e) {
                    $errors[] = "Error untuk {$employee->name}: " . $e->getMessage();
                }
            }

            DB::commit();

            return [
                'success' => true,
                'message' => "KPI berhasil dihitung untuk {$calculated} karyawan",
                'calculated' => $calculated,
                'errors' => $errors,
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    private function calculateEmployeeKPI(Employee $employee, int $month, int $year, Carbon $monthStart, Carbon $monthEnd, Carbon $currentDate): array
    {
        // Get shift dates for this employee in the month
        $dates = $employee->attendanceShiftWorks()
            ->whereYear('work_date', $year)
            ->whereMonth('work_date', $month)
            ->where('work_date', '<=', $monthEnd->format('Y-m-d'))
            ->pluck('work_date')
            ->map(fn($date) => Carbon::parse($date)->format('Y-m-d'));

        $totalDates = $dates->count() > 0 ? $dates->count() : 1;

        // Calculate Attendance KPI
        $present = Attendance::where('employee_id', $employee->id)
            ->where(function ($q) {
                $q->where('status', 'like', '%COMPLETE%')
                    ->orWhere('status', 'like', '%RUNNING%');
            })
            ->whereMonth('tanggal', $month)
            ->whereYear('tanggal', $year)
            ->where('tanggal', '<=', $monthEnd->format('Y-m-d'))
            ->count();

        $late = Attendance::where('employee_id', $employee->id)
            ->whereNotNull('late_duration_minutes')
            ->whereMonth('tanggal', $month)
            ->whereYear('tanggal', $year)
            ->where('tanggal', '<=', $monthEnd->format('Y-m-d'))
            ->count();

        $sick = Attendance::where('employee_id', $employee->id)
            ->where('status', 'like', '%SAKIT%')
            ->whereMonth('tanggal', $month)
            ->whereYear('tanggal', $year)
            ->where('tanggal', '<=', $monthEnd->format('Y-m-d'))
            ->count();

        $ontime = $present - $late;

        // Calculate special leave days
        $specialLeaveDays = $this->calculateSpecialLeaveDays($employee->id, $month, $year, $monthStart, $monthEnd, $dates);

        // Calculate annual leave days
        $annualLeaveDays = $this->calculateAnnualLeaveDays($employee->id, $month, $year, $monthStart, $monthEnd, $dates);

        // Rumus Skor Kehadiran: (Jumlah Hari Hadir + Cuti Sakit + Cuti Khusus + Cuti Tahunan yang disetujui) / Jumlah Hari Yang Telah dilalui tetapi yang ada shiftnya saja * 100
        $numeratorKehadiran = $present + $sick + $specialLeaveDays + $annualLeaveDays;
        $presentaseScorePresensi = $totalDates > 0 ? round(($numeratorKehadiran / $totalDates) * 100, 1) : 0;

        // Rumus Skor Tepat Waktu: Jumlah Hari Hadir Tepat Waktu / Jumlah Hari Hadir * 100
        $presentaseScoreOntime = $present > 0 ? round(($ontime / $present) * 100, 1) : 0;

        // Rumus Skor Keseluruhan: (50% Skor Kehadiran + 50% Skor Tepat Waktu)
        $attendanceScore = round((0.5 * $presentaseScorePresensi) + (0.5 * $presentaseScoreOntime), 1);

        // Calculate Checklist KPI (based on inspection completion)
        $checklistScore = $this->calculateChecklistScore($employee->id, $month, $year, $totalDates);

        return [
            'attendance_score' => $attendanceScore,
            'checklist_score' => $checklistScore,
        ];
    }

    private function calculateSpecialLeaveDays(int $employeeId, int $month, int $year, Carbon $monthStart, Carbon $monthEnd, $dates): int
    {
        $currentDate = now();

        return EmployeeLeaveRequest::where('employee_id', $employeeId)
            ->where('status', 'approved')
            ->whereHas('leaveType', function ($q) {
                $q->where('category', 'special_leave');
            })
            ->where(function ($q) use ($month, $year, $currentDate) {
                $q->where(function ($subQ) use ($month, $year, $currentDate) {
                    // Leave request yang start_date atau end_date ada di bulan ini
                    $subQ->where(function ($dateQ) use ($month, $year) {
                        $dateQ->whereMonth('start_date', $month)
                            ->whereYear('start_date', $year);
                    })
                    ->orWhere(function ($dateQ) use ($month, $year) {
                        $dateQ->whereMonth('end_date', $month)
                            ->whereYear('end_date', $year);
                    })
                    ->orWhere(function ($dateQ) use ($month, $year) {
                        // Leave request yang span bulan ini
                        $dateQ->where('start_date', '<=', $year . '-' . str_pad($month, 2, '0', STR_PAD_LEFT) . '-01')
                            ->where('end_date', '>=', $year . '-' . str_pad($month, 2, '0', STR_PAD_LEFT) . '-' . Carbon::create($year, $month)->daysInMonth);
                    });
                })
                ->where('start_date', '<=', $currentDate); // Hanya yang sudah dimulai
            })
            ->get()
            ->sum(function ($leaveRequest) use ($month, $year, $monthEnd, $dates) {
                return $this->calculateLeaveOverlapDays($leaveRequest, $month, $year, $monthEnd, $dates);
            });
    }

    private function calculateAnnualLeaveDays(int $employeeId, int $month, int $year, Carbon $monthStart, Carbon $monthEnd, $dates): int
    {
        $currentDate = now();

        return EmployeeLeaveRequest::where('employee_id', $employeeId)
            ->where('status', 'approved')
            ->whereHas('leaveType', function ($q) {
                $q->where('category', 'annual_leave');
            })
            ->where(function ($q) use ($month, $year, $currentDate) {
                $q->where(function ($subQ) use ($month, $year, $currentDate) {
                    // Leave request yang start_date atau end_date ada di bulan ini
                    $subQ->where(function ($dateQ) use ($month, $year) {
                        $dateQ->whereMonth('start_date', $month)
                            ->whereYear('start_date', $year);
                    })
                    ->orWhere(function ($dateQ) use ($month, $year) {
                        $dateQ->whereMonth('end_date', $month)
                            ->whereYear('end_date', $year);
                    })
                    ->orWhere(function ($dateQ) use ($month, $year) {
                        // Leave request yang span bulan ini
                        $dateQ->where('start_date', '<=', $year . '-' . str_pad($month, 2, '0', STR_PAD_LEFT) . '-01')
                            ->where('end_date', '>=', $year . '-' . str_pad($month, 2, '0', STR_PAD_LEFT) . '-' . Carbon::create($year, $month)->daysInMonth);
                    });
                })
                ->where('start_date', '<=', $currentDate); // Hanya yang sudah dimulai
            })
            ->get()
            ->sum(function ($leaveRequest) use ($month, $year, $monthEnd, $dates) {
                return $this->calculateLeaveOverlapDays($leaveRequest, $month, $year, $monthEnd, $dates);
            });
    }

    private function calculateLeaveOverlapDays($leaveRequest, int $month, int $year, Carbon $monthEnd, $dates): int
    {
        $startDate = Carbon::parse($leaveRequest->start_date);
        $endDate = Carbon::parse($leaveRequest->end_date);
        $monthStart = Carbon::create($year, $month, 1);
        $monthEndDate = Carbon::create($year, $month, Carbon::create($year, $month)->daysInMonth);
        $currentDateCarbon = Carbon::parse($monthEnd->format('Y-m-d'));

        if ($endDate->gt($currentDateCarbon)) {
            $endDate = $currentDateCarbon;
        }

        $overlapStart = max($startDate, $monthStart);
        $overlapEnd = min($endDate, $monthEndDate);

        if ($overlapStart->lte($overlapEnd)) {
            $overlapDays = 0;
            $tempDate = $overlapStart->copy();
            while ($tempDate->lte($overlapEnd)) {
                if ($dates->contains($tempDate->format('Y-m-d'))) {
                    $overlapDays++;
                }
                $tempDate->addDay();
            }
            return $overlapDays;
        }
        return 0;
    }

    private function calculateChecklistScore(int $employeeId, int $month, int $year, int $totalDates): float
    {
        // Count inspections where employee submitted answers in this month
        $totalInspections = Answer::where('employee_id', $employeeId)
            ->whereHas('inspection', function ($q) use ($month, $year) {
                $q->whereMonth('submit_date', $month)
                    ->whereYear('submit_date', $year);
            })
            ->whereMonth('created_at', $month)
            ->whereYear('created_at', $year)
            ->distinct('inspection_id')
            ->count('inspection_id');

        // Get total expected inspections (simplified: based on work days)
        // You may need to adjust this based on your business logic
        $expectedInspections = max($totalDates, 1); // At least 1 per work day

        $checklistCount = Employee::find($employeeId)->checklists()->count();

        // Prevent division by zero
        $denominator = $expectedInspections * $checklistCount;
        if ($denominator <= 0) {
            return 0;
        }

        $checklistScore = round(($totalInspections / $denominator) * 100, 1);

        // Cap at 100
        return min($checklistScore, 100);
    }
}

