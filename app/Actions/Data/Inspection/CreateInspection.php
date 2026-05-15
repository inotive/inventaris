<?php

namespace App\Actions\Data\Inspection;

use App\Models\Answer;
use App\Models\Checklist;
use App\Models\Inspection;
use App\Models\VehicleHistoryOdometer;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CreateInspection
{
    /**
     * Execute the action to create or update an inspection
     *
     * @param \App\Models\User $user
     * @param array $data
     * @return array
     */
    public function execute($user, array $data): array
    {
        Log::info("CreateInspection - Execute Start", [
            'user_id' => $user->id,
            'employee_id' => $user->employee->id ?? null,
            'checklist_id' => $data['checklist_id'] ?? null,
            'inspection_id' => $data['inspection_id'] ?? null,
            'status' => $data['status'] ?? null
        ]);

        return DB::transaction(function () use ($user, $data) {
            // Check if user has access to checklist
            $checklist = Checklist::findOrFail($data['checklist_id']);
            $checklistEmployee = $checklist->employees()->where('employee_id', $user->employee->id)->first();

            Log::info("CreateInspection - Checklist Access Check", [
                'user_id' => $user->id,
                'employee_id' => $user->employee->id,
                'checklist_id' => $checklist->id,
                'has_access' => $checklistEmployee ? true : false
            ]);

            if (!$checklistEmployee) {
                Log::warning("CreateInspection - Access Denied", [
                    'user_id' => $user->id,
                    'employee_id' => $user->employee->id,
                    'checklist_id' => $checklist->id
                ]);
                throw new \Exception('You are not authorized to access this checklist');
            }

            // Determine model type
            $modelType = match ($data['model_type'] ?? null) {
                'employee' => 'App\Models\Employee',
                'vehicle'  => 'App\Models\Vehicle',
                default    => null,
            };

            Log::info("CreateInspection - Model Type Determined", [
                'model_type_input' => $data['model_type'] ?? null,
                'model_type_resolved' => $modelType,
                'model_id' => $data['model_id'] ?? null
            ]);

            // Create or update inspection
            if (!empty($data['inspection_id'])) {
                $inspection = Inspection::findOrFail($data['inspection_id']);
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

                Log::info("CreateInspection - Inspection Updated", [
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

                Log::info("CreateInspection - Inspection Created", [
                    'inspection_id' => $inspection->id,
                    'inspection_number' => $inspection->inspection_number,
                    'status' => $inspection->status
                ]);
            }

            // Update vehicle odometer if applicable
            if (($data['model_type'] ?? null) === 'vehicle' && !empty($data['model_id']) && isset($data['odometer'])) {
                Log::info("CreateInspection - Updating Vehicle Odometer", [
                    'vehicle_id' => $data['model_id'],
                    'odometer' => $data['odometer']
                ]);
                $this->updateVehicleOdometer($user, $data);
            }

            // Process answers
            $savedAnswers = [];
            if (!empty($data['answers'])) {
                Log::info("CreateInspection - Processing Answers", [
                    'inspection_id' => $inspection->id,
                    'answers_count' => count($data['answers'])
                ]);

                foreach ($data['answers'] as $index => $answerData) {
                    $answer = $this->createOrUpdateAnswer([
                        'inspection_id' => $inspection->id,
                        'question_id'   => $answerData['question_id'],
                        'employee_id'   => $user->employee->id,
                        'answer'        => $answerData['answer'],
                        'note'          => $answerData['note'] ?? null,
                        'attachment'    => $answerData['attachment'] ?? null,
                    ], $answerData['answer_id'] ?? null);

                    $savedAnswers[] = $answer;
                }

                Log::info("CreateInspection - Answers Processed", [
                    'inspection_id' => $inspection->id,
                    'saved_answers_count' => count($savedAnswers)
                ]);
            }

            // Reload inspection with relationships
            $inspection->load(['model', 'submittedBy']);

            Log::info("CreateInspection - Execute Complete", [
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
     * Update vehicle odometer history
     *
     * @param \App\Models\User $user
     * @param array $data
     * @return void
     */
    private function updateVehicleOdometer($user, array $data): void
    {
        $vehicleId = (int) $data['model_id'];

        Log::info('Vehicle odometer update - start', [
            'vehicle_id' => $vehicleId,
            'submitted_km' => $data['km_terbaru'] ?? null,
            'submit_date_raw' => $data['submit_date'] ?? null,
            'user_id' => $user->id,
        ]);

        $previousKm = VehicleHistoryOdometer::where('vehicle_id', $vehicleId)
            ->orderByDesc('tanggal')
            ->orderByDesc('id')
            ->value('current_km') ?? 0;

        Log::info('Vehicle odometer update - previous km fetched', [
            'vehicle_id' => $vehicleId,
            'previous_km' => (int) $previousKm,
        ]);

        $tanggal = !empty($data['submit_date'])
            ? date('Y-m-d', strtotime($data['submit_date']))
            : now()->toDateString();

        $payload = [
            'vehicle_id'  => $vehicleId,
            'tanggal'     => $tanggal,
            'last_km'     => (int) $previousKm,
            'current_km'  => (int) $data['km_terbaru'],
            'created_by'  => $user->id,
        ];

        $odometerHistory = VehicleHistoryOdometer::create($payload);

        Log::info('Vehicle odometer update - history created', [
            'odometer_history_id' => $odometerHistory->id ?? null,
            'vehicle_id' => $vehicleId,
            'tanggal' => $tanggal,
            'last_km' => $payload['last_km'],
            'current_km' => $payload['current_km'],
            'created_by' => $payload['created_by'],
        ]);
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

            Log::info("CreateInspection - Answer Updated", [
                'answer_id' => $answer->id,
                'inspection_id' => $answerData['inspection_id'],
                'question_id' => $answerData['question_id'],
                'employee_id' => $answerData['employee_id']
            ]);

            return $answer;
        }

        $answer = Answer::create($answerData);

        Log::info("CreateInspection - Answer Created", [
            'answer_id' => $answer->id,
            'inspection_id' => $answerData['inspection_id'],
            'question_id' => $answerData['question_id'],
            'employee_id' => $answerData['employee_id']
        ]);

        return $answer;
    }
}
