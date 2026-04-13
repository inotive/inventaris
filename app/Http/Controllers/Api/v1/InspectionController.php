<?php

namespace App\Http\Controllers\Api\v1;

use App\Actions\Data\Inspection\CreateGroupInspection;
use App\Actions\Data\Inspection\CreateInspection;
use App\Helpers\ResponseFormatter;
use App\Http\Controllers\Controller;
use App\Http\Requests\InspectionRequest;
use App\Http\Resources\InspectionResource;
use App\Http\Resources\QuestionResource;
use App\Models\Inspection;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class InspectionController extends Controller
{

    public function showDraftByChecklistId(Request $request, $inspectionId, $id)
    {
        Log::info('[InspectionController] showDraftByChecklistId', ['id' => $id]);
        Log::info('[InspectionController] inspectionId', ['inspectionId' => $inspectionId]);
        $inspection = Auth::user()->employee->inspections()->with(['model', 'location', 'submittedBy'])
            ->where('checklist_id', $id)
            ->when($inspectionId, function ($query) use ($inspectionId) {
                $query->where('id', $inspectionId);
            })
            ->where('status', 'draft')
            ->first();

        if (!$inspection) {
            return ResponseFormatter::error('Inspection not found', 404);
        }

        return ResponseFormatter::success(
            new InspectionResource($inspection),
            'Inspection with checklist ID retrieved successfully'
        );
    }
    /**
     * Get inspection history submitted by the current authenticated user
     * GET /v1/inspections/history
     */
    public function history(Request $request): JsonResponse
    {
        $user = Auth::user();
        if (!$user) {
            return ResponseFormatter::error('Unauthenticated', 401);
        }
        $query = Inspection::query()
            ->with(['checklist', 'submittedBy.employee.department'])
            ->where(function ($q) use ($user) {
                // Show inspections submitted by current user
                $q->where('submitted_by', $user->id)
                    // OR show on-progress inspections from same department
                    ->orWhere(function ($subQuery) use ($user) {
                    $subQuery->where('status', 'on_progress');

                    if ($user->employee && $user->employee->department_id) {
                        $subQuery->whereHas('submittedBy.employee', function ($empQuery) use ($user) {
                            $empQuery->where('department_id', $user->employee->department_id);
                        });
                    }
                });
            })
            ->orderByDesc('submit_date')
            ->orderByDesc('created_at');

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }
        if ($request->filled('date_from')) {
            $query->whereDate('submit_date', '>=', $request->input('date_from'));
        }
        if ($request->filled('date_to')) {
            $query->whereDate('submit_date', '<=', $request->input('date_to'));
        }

        $limit = (int) $request->input('limit', 15);
        $rows = $query->paginate($limit)->withQueryString();

        return ResponseFormatter::successWithPagination(
            InspectionResource::collection($rows),
            'inspections',
            'Inspection history fetched',
            $rows->total(),
            $rows->count(),
            $rows->perPage(),
            $rows->currentPage(),
            $rows->lastPage()
        );
    }

    /**
     * Get question categories with answered/total questions count for inspection
     * GET /v1/inspections/{id}/categories
     */
    public function categories($id): JsonResponse
    {
        $user = Auth::user();
        if (!$user) {
            return ResponseFormatter::error('Unauthenticated', 401);
        }

        $inspection = Inspection::with([
            'checklist.questions.category:id,name',
            'answers.question.category:id,name'
        ])->find($id);

        if (!$inspection) {
            return ResponseFormatter::error('Inspection not found', 404);
        }

        // Check if user has access to this inspection
        if ($inspection->submitted_by !== $user->id) {
            return ResponseFormatter::error('Unauthorized access', 403);
        }

        // Get all questions grouped by category
        $questionsByCategory = $inspection->checklist->questions->groupBy('category.name');

        // Get answered questions grouped by category
        $answeredQuestionsByCategory = $inspection->answers->groupBy('question.category.name');

        $categoryData = $questionsByCategory->map(function ($questions, $categoryName) use ($answeredQuestionsByCategory) {
            $totalQuestions = $questions->count();
            $answeredQuestions = $answeredQuestionsByCategory->get($categoryName, collect())->count();

            return [
                'category_name' => $categoryName ?? 'Uncategorized',
                'total_questions' => $totalQuestions,
                'answered_questions' => $answeredQuestions,
                'completion_percentage' => $totalQuestions > 0 ? round(($answeredQuestions / $totalQuestions) * 100, 2) : 0
            ];
        })->values();

        return ResponseFormatter::success($categoryData, 'Question categories retrieved successfully');
    }

    /**
     * Get detailed inspection history by inspection ID
     * GET /v1/inspections/{id}/detail
     */
    public function detail($id): JsonResponse
    {
        $inspection = Inspection::with([
            'checklist.questions', // Load questions dalam checklist
            'location',
            'submittedBy',
            'model',
        ])->find($id);

        if (!$inspection) {
            return ResponseFormatter::error('Inspection not found', 404);
        }

        $user = Auth::user();
        $employeeId = $user->employee->id;

        // Always include answers in detail response (3rd param = true)
        $resource = new InspectionResource($inspection, $employeeId, true);

        return ResponseFormatter::success($resource, 'Inspection detail retrieved successfully');
    }

    public function store(InspectionRequest $request): JsonResponse
    {
        try {
            $user = Auth::user();
            $data = $request->validated();

            Log::info("[InspectionController] Store Single Inspection - Start", [
                'user_id' => $user->id,
                'employee_id' => $user->employee->id ?? null,
                'checklist_id' => $data['checklist_id'] ?? null,
                'inspection_id' => $data['inspection_id'] ?? null,
                'status' => $data['status'] ?? null,
                'model_type' => $data['model_type'] ?? null,
                'model_id' => $data['model_id'] ?? null,
                'answers_count' => isset($data['answers']) ? count($data['answers']) : 0
            ]);

            $result = app(CreateInspection::class)->execute($user, $data);

            $resource = new InspectionResource($result['inspection']);
            $responseData = $resource->toArray($request);

            Log::info("[InspectionController] Store Single Inspection - Success", [
                'user_id' => $user->id,
                'inspection_id' => $result['inspection']->id,
                'inspection_number' => $result['inspection']->inspection_number,
                'status' => $result['inspection']->status,
                'message' => $result['message'],
                'response_has_id' => isset($responseData['id']),
                'response_has_checklist_id' => isset($responseData['checklist_id']),
                'response_has_vehicle' => isset($responseData['vehicle']),
                'response_has_employee' => isset($responseData['employee'])
            ]);

            // Send notification to supervisors when inspection is submitted (not draft)
            if ($result['inspection']->status === 'submitted') {
                try {
                    $inspection = $result['inspection']->load(['checklist', 'model']);
                    $modelName = '';
                    if ($inspection->model) {
                        $modelName = $inspection->model->name ?? $inspection->model->plate_number ?? '';
                    }

                    // Ensure checklist name is not null
                    $checklistName = $inspection->checklist ? $inspection->checklist->name : 'Checklist';

                    Log::info('[InspectionController] Sending inspection notification', [
                        'inspection_id' => $inspection->id,
                        'checklist_loaded' => $inspection->checklist ? 'yes' : 'no',
                        'checklist_id' => $inspection->checklist_id,
                        'checklist_name' => $checklistName,
                        'checklist_object' => $inspection->checklist ? 'exists' : 'null',
                    ]);

                    app(\App\Services\NotificationService::class)->notifySupervisorsOnInspectionSubmit(
                        $inspection->id,
                        $user,
                        [
                            'checklist_name' => $checklistName,
                            'inspection_number' => $inspection->inspection_number,
                            'model_type' => $inspection->model_type,
                            'model_name' => $modelName,
                        ]
                    );
                } catch (\Throwable $e) {
                    Log::error('Failed to send inspection notification', [
                        'inspection_id' => $result['inspection']->id,
                        'error' => $e->getMessage(),
                        'trace' => $e->getTraceAsString(),
                    ]);
                }
            }

            return ResponseFormatter::success($resource, $result['message']);
        } catch (\Exception $e) {
            Log::error("[InspectionController] Store Single Inspection - Error", [
                'user_id' => $user->id ?? null,
                'error_message' => $e->getMessage(),
                'error_file' => $e->getFile(),
                'error_line' => $e->getLine(),
                'stack_trace' => $e->getTraceAsString()
            ]);
            return ResponseFormatter::error($e->getMessage(), 500);
        }
    }

    public function storeGroup(InspectionRequest $request): JsonResponse
    {
        try {
            $user = Auth::user();
            $data = $request->validated();
            $employeeName = $user->employee->name ?? $user->name ?? null;

            Log::info("[InspectionController] Store Group Inspection - Start", [
                'user_id' => $user->id,
                'employee_id' => $user->employee->id ?? null,
                'employee_name' => $employeeName,
                'checklist_id' => $data['checklist_id'] ?? null,
                'inspection_id' => $data['inspection_id'] ?? null,
                'status' => $data['status'] ?? null,
                'model_type' => $data['model_type'] ?? null,
                'model_id' => $data['model_id'] ?? null,
                'answers_count' => isset($data['answers']) ? count($data['answers']) : 0,
                'request_data' => $data
            ]);

            $result = app(CreateGroupInspection::class)->execute($user, $data);

            $resource = new InspectionResource($result['inspection']);
            $responseData = $resource->toArray($request);

            Log::info("[InspectionController] Store Group Inspection - Success " . ($employeeName ?? 'Unknown'), [
                'user_id' => $user->id,
                'employee_name' => $employeeName,
                'inspection_id' => $result['inspection']->id,
                'inspection_number' => $result['inspection']->inspection_number,
                'status' => $result['inspection']->status,
                'message' => $result['message'],
                'response_has_id' => isset($responseData['id']),
                'response_has_checklist_id' => isset($responseData['checklist_id']),
                'response_has_vehicle' => isset($responseData['vehicle']),
                'response_has_employee' => isset($responseData['employee'])
            ]);

            // Send notification to supervisors when inspection is submitted (not draft)
            if ($result['inspection']->status === 'submitted') {
                try {
                    $inspection = $result['inspection']->load(['checklist', 'model']);
                    $modelName = '';
                    if ($inspection->model) {
                        $modelName = $inspection->model->name ?? $inspection->model->plate_number ?? '';
                    }

                    // Ensure checklist name is not null
                    $checklistName = $inspection->checklist ? $inspection->checklist->name : 'Checklist';

                    Log::info('[InspectionController] Sending group inspection notification', [
                        'inspection_id' => $inspection->id,
                        'employee_name' => $employeeName,
                        'checklist_loaded' => $inspection->checklist ? 'yes' : 'no',
                        'checklist_id' => $inspection->checklist_id,
                        'checklist_name' => $checklistName,
                        'checklist_object' => $inspection->checklist ? 'exists' : 'null',
                    ]);

                    app(\App\Services\NotificationService::class)->notifySupervisorsOnInspectionSubmit(
                        $inspection->id,
                        $user,
                        [
                            'checklist_name' => $checklistName,
                            'inspection_number' => $inspection->inspection_number,
                            'model_type' => $inspection->model_type,
                            'model_name' => $modelName,
                        ]
                    );
                } catch (\Throwable $e) {
                    Log::error('Failed to send inspection notification', [
                        'inspection_id' => $result['inspection']->id,
                        'error' => $e->getMessage(),
                        'trace' => $e->getTraceAsString(),
                    ]);
                }
            }

            return ResponseFormatter::success($resource, $result['message']);
        } catch (\Exception $e) {
            Log::error("[InspectionController] Store Group Inspection - Error", [
                'user_id' => $user->id ?? null,
                'error_message' => $e->getMessage(),
                'error_file' => $e->getFile(),
                'error_line' => $e->getLine(),
                'stack_trace' => $e->getTraceAsString()
            ]);
            return ResponseFormatter::error($e->getMessage(), 500);
        }
    }



    /**
     * Get answers for a specific inspection
     * GET /v1/inspections/{id}/answers
     */
    public function answers($id): JsonResponse
    {
        $inspection = Inspection::with([
            'checklist.questions.options',
            'answers.question',
        ])->find($id);

        if (!$inspection) {
            return ResponseFormatter::error('Inspection not found', 404);
        }

        // Map answers dengan detail pertanyaan
        $answers = $inspection->answers->map(function ($answer) {
            return [
                'id' => $answer->id,
                'question_id' => $answer->question_id,
                'question_text' => $answer->question->question ?? null,
                'answer' => $answer->answer,
                'note' => $answer->note,
                'created_at' => $answer->created_at,
            ];
        });

        return ResponseFormatter::success([
            'inspection_id' => $inspection->id,
            'checklist_name' => $inspection->checklist->name ?? null,
            'status' => $inspection->status,
            'answers' => $answers,
        ], 'Inspection answers retrieved successfully');
    }

}
