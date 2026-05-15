<?php

namespace Database\Seeders;

use App\Models\AttendanceShiftWork;
use App\Models\Employee;
use App\Models\Shift;
use Illuminate\Database\Seeder;

class AttendanceShiftWorkSeeder extends Seeder
{
    public function run(): void
    {
        $employee = Employee::limit(20)->get();
        $shifts = Shift::all();

        if ($employee->isNotEmpty() && $shifts->isNotEmpty()) {
            $now = now();
            $data = [];

            foreach ($employee as $user) {
                for ($day = 1; $day <= 30; $day++) {
                    $shift = $shifts->random();
                    $workDate = $now->copy()->startOfMonth()->addDays($day - 1);

                    $data[] = [
                        'employee_id' => $user->id,
                        'shift_id' => $shift->id,
                        'work_date' => $workDate->toDateString(),
                        'created_at' => $now,
                        'updated_at' => $now,
                    ];
                }
            }

            AttendanceShiftWork::insert($data);
        }
    }
}
