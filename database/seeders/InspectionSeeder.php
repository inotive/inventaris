<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Answer;
use App\Models\Branch;
use App\Models\Question;
use App\Models\Checklist;
use App\Models\Vehicle;
use App\Models\Inspection;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Illuminate\Support\Carbon;
use Illuminate\Database\Seeder;

class InspectionSeeder extends Seeder
{
    public function run(): void
    {
        $checklist = Checklist::pluck('id')->toArray();
        if (empty($checklist)) {
            $checklist = [Checklist::factory()->create()->id];
        }

        $branch    = Branch::pluck('id')->toArray();
        $vehicleIds = Vehicle::pluck('id')->toArray();
        $submitter = User::pluck('id')->toArray();
        $approver  = User::pluck('id')->toArray();

        // create 5 inspections
        for ($i = 0; $i < 5; $i++) {
            $submitAt = Carbon::now()
                ->subDays(rand(0, 10))
                ->setTime(rand(7, 17), [0, 15, 30, 45][array_rand([0, 1, 2, 3])]);

            $inspection = Inspection::create([
                'checklist_id'      => Arr::random($checklist),
                'inspection_number' => 'INSP-' . strtoupper(Str::random(6)),
                'submit_date'       => $submitAt,
                'submitted_by'      => Arr::random($submitter),
                'status'            => ['draft', 'submitted', 'approved', 'rejected'][array_rand([0, 1, 2, 3])],
                'approved_by'       => Arr::random($approver),
                'approved_date'     => $submitAt->copy()->addHours(rand(1, 48)),
                'remarks'           => fake()->sentence(8),
                'location_id'       => Arr::random($branch),
                // link inspection to a vehicle
                'model_type'        => Vehicle::class,
                'model_id'          => !empty($vehicleIds) ? Arr::random($vehicleIds) : null,
            ]);

            // pick up to 3 random questions of this inspection's checklist
            $questions = Question::where('checklist_id', $inspection->checklist_id)
                ->inRandomOrder()
                ->limit(3)
                ->get();

            foreach ($questions as $q) {
                Answer::create([
                    'question_id'   => $q->id,
                    'employee_id'   => Arr::random($submitter),
                    'inspection_id' => $inspection->id,
                    'answer'        => fake()->boolean() ? 'Ya' : 'Tidak',
                ]);
            }
        }
    }
}
