<?php

namespace App\Actions\Data\Inspection;

use App\Models\Answer;
use App\Models\Checklist;
use App\Models\Inspection;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CreateGroupInspection
{
    /**
     * Execute the action to create or update a group inspection
     *
     * @param \App\Models\User $user
     * @param array $data
     * @return array
     */
    public function execute($user, array $data): array
    {
        Log::info("CreateGroupInspection - Execute Start", [
            'user_id' => $user->id,
            'employee_id' => $user->employee->id ?? null,
            'checklist_id' => $data['checklist_id'] ?? null,
            'inspection_id' => $data['inspection_id'] ?? null,
            'status' => $data['status'] ?? null
        ]);

        return DB::transaction(function () use ($user, $data) {
            // Determine model type
            $modelType = match ($data['model_type'] ?? null) {
                'employee' => 'App\Models\Employee',
                'vehicle'  => 'App\Models\Vehicle',
                default    => null,
            };

            Log::info("CreateGroupInspection - Model Type Determined", [
                'model_type_input' => $data['model_type'] ?? null,
                'model_type_resolved' => $modelType,
                'model_id' => $data['model_id'] ?? null
            ]);

            // Create or update inspection
            if (!empty($data['inspection_id'])) {
                $inspection = Inspection::findOrFail($data['inspection_id']);
                // dd($inspection->toArray());
                $inspection->update([
                    'checklist_id'      => $data['checklist_id'],
                    'submit_date'       => $data['submit_date'] ?? null,
                    'submitted_by'      => $user->id,
                    'status'            => $data['status'],
                    'remarks'           => $data['remarks'] ?? null,
                    'location_id'       => $data['location_id'] ?? null,
                    'model_type'        => $modelType,
                    'model_id'          => $data['model_id'] ?? null,
                ]);
                $message = 'Inspection berhasil diupdate!';

                Log::info("CreateGroupInspection - Inspection Updated", [
                    'inspection_id' => $inspection->id,
                    'inspection_number' => $inspection->inspection_number,
                    'status' => $inspection->status
                ]);
            } else {
                $inspectionNumber = $this->generateInspectionNumber();
                $inspection = Inspection::create([
                    'checklist_id'      => $data['checklist_id'],
                    'inspection_number' => $inspectionNumber,
                    'submit_date'       => $data['submit_date'] ?? null,
                    'submitted_by'      => $user->id,
                    'status'            => $data['status'],
                    'remarks'           => $data['remarks'] ?? null,
                    'location_id'       => $data['location_id'] ?? null,
                    'model_type'        => $modelType,
                    'model_id'          => $data['model_id'] ?? null,
                ]);
                $message = 'Inspection berhasil dibuat!';

                Log::info("CreateGroupInspection - Inspection Created", [
                    'inspection_id' => $inspection->id,
                    'inspection_number' => $inspection->inspection_number,
                    'status' => $inspection->status
                ]);
            }

            // Get checklist with related employees
            $checklist = Checklist::with('employees')->findOrFail($data['checklist_id']);
            $checklistEmployees = $checklist->employees;

            Log::info("CreateGroupInspection - Checklist Employees Loaded", [
                'user_id' => $user->id,
                'checklist_id' => $checklist->id,
                'checklist_name' => $checklist->name,
                'employees_count' => $checklistEmployees->count(),
                'employee_ids' => $checklistEmployees->pluck('id')->toArray()
            ]);

            // Determine which employees to process
            $employeesToProcess = $this->getEmployeesToProcess($checklistEmployees, $user);

            Log::info("CreateGroupInspection - Employees To Process", [
                'employees_count' => $employeesToProcess->count(),
                'employee_ids' => $employeesToProcess->pluck('id')->toArray()
            ]);

            // Process answers for all related employees
            $savedAnswers = $this->processAnswersForEmployees($inspection, $data, $employeesToProcess);

            Log::info("CreateGroupInspection - Answers Processed", [
                'inspection_id' => $inspection->id,
                'saved_answers_count' => count($savedAnswers)
            ]);

            // Reload inspection with relationships
            $inspection->load(['model', 'submittedBy']);

            Log::info("CreateGroupInspection - Execute Complete", [
                'inspection_id' => $inspection->id,
                'inspection_number' => $inspection->inspection_number,
                'status' => $inspection->status,
                'message' => $message
            ]);

            return [
                'inspection' => $inspection,
                'message' => $message,
            ];
        });
    }

    /**
     * Generate sequential inspection number: INSP-YYYYMMDD-XXXXX
     *
     * @return string
     */
    private function generateInspectionNumber(): string
    {
        $today = now()->format('Ymd');
        $prefix = 'INSP-' . $today . '-';

        // Get last inspection number for today
        $lastInspection = Inspection::where('inspection_number', 'like', $prefix . '%')
            ->orderByDesc('inspection_number')
            ->first();

        if ($lastInspection) {
            // Extract the sequential number and increment
            $lastNumber = (int) substr($lastInspection->inspection_number, -5);
            $newNumber = $lastNumber + 1;
        } else {
            // Start from 1
            $newNumber = 1;
        }

        return $prefix . str_pad($newNumber, 5, '0', STR_PAD_LEFT);
    }

    /**
     * Get employees to process based on checklist employees or fallback to current user
     *
     * @param Collection $checklistEmployees
     * @param \App\Models\User $user
     * @return Collection
     */
    private function getEmployeesToProcess(Collection $checklistEmployees, $user): Collection
    {
        $employeesToProcess = $checklistEmployees->isNotEmpty()
            ? $checklistEmployees
            : collect([$user->employee])->filter();

        if ($employeesToProcess->isEmpty()) {
            Log::warning("CreateGroupInspection - No Employees Found", [
                'user_id' => $user->id,
                'checklist_employees_count' => $checklistEmployees->count(),
                'user_has_employee' => $user->employee !== null
            ]);
        } else {
            Log::info("CreateGroupInspection - Employees Determined", [
                'user_id' => $user->id,
                'source' => $checklistEmployees->isNotEmpty() ? 'checklist' : 'user',
                'employees_count' => $employeesToProcess->count(),
                'employee_ids' => $employeesToProcess->pluck('id')->toArray()
            ]);
        }

        return $employeesToProcess;
    }

    /**
     * Process answers for all employees
     *
     * @param Inspection $inspection
     * @param array $data
     * @param Collection $employeesToProcess
     * @return array
     */
    private function processAnswersForEmployees(Inspection $inspection, array $data, Collection $employeesToProcess): array
    {
        $savedAnswers = [];

        if (empty($data['answers']) || $employeesToProcess->isEmpty()) {
            Log::warning("CreateGroupInspection - No Answers or Employees to Process", [
                'inspection_id' => $inspection->id,
                'has_answers' => !empty($data['answers']),
                'employees_count' => $employeesToProcess->count()
            ]);
            return $savedAnswers;
        }

        Log::info("CreateGroupInspection - Processing Answers for Employees", [
            'inspection_id' => $inspection->id,
            'answers_count' => count($data['answers']),
            'employees_count' => $employeesToProcess->count(),
            'total_operations' => count($data['answers']) * $employeesToProcess->count()
        ]);

        foreach ($data['answers'] as $answerIndex => $answerData) {
            Log::info("CreateGroupInspection - Processing Answer", [
                'inspection_id' => $inspection->id,
                'answer_index' => $answerIndex,
                'question_id' => $answerData['question_id'] ?? null,
                'answer_value' => $answerData['answer'] ?? null
            ]);

            foreach ($employeesToProcess as $employeeIndex => $employee) {
                $answerId = null;
                $answer = Answer::where('id', $answerData['answer_id'])->first();

                if ($answer) {
                    $answerId = $answer->id;
                    Log::info("CreateGroupInspection - Existing Answer Found", [
                        'inspection_id' => $inspection->id,
                        'answer_id' => $answerId,
                        'question_id' => $answerData['question_id'],
                        'employee_id' => $employee->id
                    ]);
                }

                $answer = $this->createOrUpdateAnswer([
                    'inspection_id' => $inspection->id,
                    'question_id'   => $answerData['question_id'],
                    'employee_id'   => $employee->id,
                    'answer'        => $answerData['answer'],
                    'note'          => $answerData['note'] ?? null,
                    'attachment'    => $answerData['attachment'] ?? null,
                ], $answerId);

                Log::info("CreateGroupInspection - Answer Saved", [
                    'inspection_id' => $inspection->id,
                    'answer_id' => $answer->id,
                    'question_id' => $answer->question_id,
                    'employee_id' => $answer->employee_id,
                    'is_update' => $answerId !== null
                ]);

                $savedAnswers[] = $answer;
            }
        }

        Log::info("CreateGroupInspection - All Answers Processed", [
            'inspection_id' => $inspection->id,
            'total_saved_answers' => count($savedAnswers)
        ]);

        return $savedAnswers;
    }

    /**
     * Create or update an answer for an inspection
     *
     * @param array $answerData
     * @param int|null $answerId
     * @return Answer
     */
    private function createOrUpdateAnswer(array $answerData, ?int $answerId = null): Answer
    {
        if ($answerId) {
            $answer = Answer::findOrFail($answerId);
            $answer->update($answerData);

            Log::info("CreateGroupInspection - Answer Updated", [
                'answer_id' => $answer->id,
                'inspection_id' => $answerData['inspection_id'],
                'question_id' => $answerData['question_id'],
                'employee_id' => $answerData['employee_id']
            ]);

            return $answer;
        }

        $answer = Answer::create($answerData);

        Log::info("CreateGroupInspection - Answer Created", [
            'answer_id' => $answer->id,
            'inspection_id' => $answerData['inspection_id'],
            'question_id' => $answerData['question_id'],
            'employee_id' => $answerData['employee_id']
        ]);

        return $answer;
    }
}

