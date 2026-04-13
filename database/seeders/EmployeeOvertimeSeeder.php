<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;
use App\Models\Employee;

class EmployeeOvertimeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Use current month timeframe
        $start = now()->startOfMonth();
        $end = now()->endOfMonth();

        // pick up to 200 employees to seed OT for
        $employees = Employee::inRandomOrder()->limit(200)->get(['id']);
        if ($employees->isEmpty()) {
            return; // nothing to seed
        }

        $statuses = ['pending', 'approved', 'rejected'];
        $rows = [];

        foreach ($employees as $emp) {
            // number of OT records for this employee in current month
            $count = rand(0, 5);
            for ($i = 0; $i < $count; $i++) {
                $date = Carbon::create(
                    $start->year,
                    $start->month,
                    rand(1, (int) $start->daysInMonth)
                )->toDateString();

                // Random start and end time in after-hours period
                $startHour = rand(17, 20); // 5 PM - 8 PM
                $startMinute = [0, 15, 30, 45][array_rand([0, 1, 2, 3])];
                $duration = rand(1, 6) + (rand(0, 1) ? 0.5 : 0); // 1.0 - 6.5 hours
                $endTs = Carbon::parse($date . ' ' . $startHour . ':' . $startMinute)
                    ->copy()
                    ->addMinutes((int) round($duration * 60));

                $status = $statuses[array_rand($statuses)];

                $rows[] = [
                    'employee_id' => $emp->id,
                    'date' => $date,
                    'start_time' => sprintf('%02d:%02d:00', $startHour, $startMinute),
                    'end_time' => $endTs->format('H:i:s'),
                    'duration_hours' => $duration,
                    'reason' => fake()->sentence(6),
                    'status' => $status,
                    'approved_by' => null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
        }

        // Chunk insert for performance
        foreach (array_chunk($rows, 1000) as $chunk) {
            DB::table('employee_overtimes')->insert($chunk);
        }
    }
}
