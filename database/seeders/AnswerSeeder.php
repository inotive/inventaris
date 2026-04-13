<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Question;
use App\Models\Employee;

class AnswerSeeder extends Seeder
{
    public function run(): void
    {
        $questions = Question::limit(10)->get();
        if ($questions->isEmpty()) return;

        $employeeId = Employee::value('id'); // nullable
        $now = now();

        foreach ($questions as $q) {
            DB::table('answers')->updateOrInsert(
                [
                    'question_id' => $q->id,
                    'employee_id' => $employeeId,
                ],
                [
                    'answer' => match ($q->answer_type) {
                        'boolean' => 'yes',
                        'number'  => '10',
                        default   => 'OK',
                    },
                    'created_at' => $now,
                    'updated_at' => $now,
                ]
            );
        }
    }
}
