<?php

namespace App\Http\Controllers\Api\v1;

use App\Helpers\ResponseFormatter;
use App\Http\Controllers\Controller;
use App\Http\Resources\ChecklistResource;
use App\Http\Resources\QuestionResource;
use App\Models\Checklist;
use App\Models\Inspection;
use App\Models\Answer;
use App\Models\Employee;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use Illuminate\Support\Facades\Http;
use DragonCode\Contracts\Cashier\Http\Response;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ChecklistController extends Controller
{
    public function index(Request $request)
    {
        $query = Checklist::with(['category', 'department']);

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        if ($request->has('department_id')) {
            $query->where('department_id', $request->department_id);
        }

        if ($request->has('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        if ($request->has('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', "%{$request->search}%")
                    ->orWhere('description', 'like', "%{$request->search}%");
            });
        }

        $limit = $request->get('limit', 10);
        $checklists = $query->paginate($limit);

        $resourceData = ChecklistResource::collection($checklists->items());

        return ResponseFormatter::successWithPagination(
            $resourceData,
            'checklists',
            'List of Checklists',
            $checklists->total(),
            $checklists->count(),
            $checklists->perPage(),
            $checklists->currentPage(),
            $checklists->lastPage()
        );
    }

    public function show(Request $request, Checklist $checklist)
    {
        $isProgress = $request->input('is_progress', false);
        $inspectionId = $request->input('inspection_id', null);
        // Load questions untuk checklist
        $checklist->load('questions');

        $user = Auth::user();
        $employeeId = $user->employee->id ?? null;

        if (!$employeeId || !$user->employee) {
            return ResponseFormatter::error('Employee not found', 404);
        }

        // Cari latest draft inspection untuk user ini
        $latestInspection = Inspection::where('checklist_id', $checklist->id)
            ->where('submitted_by', $user->id)
            ->where('status', 'draft')
            ->when($inspectionId, function ($query) use ($inspectionId) {
                $query->where('id', $inspectionId);
            })
            ->latest('submit_date')
            ->first();

        // Pass employeeId dan inspectionId ke ChecklistResource
        $resource = new ChecklistResource(
            $checklist,
            $employeeId,
            $latestInspection ? $latestInspection->id : null,
            $isProgress
        );

        // Tambahkan inspection data di level root
        $data = $resource->toArray(request());

        return ResponseFormatter::success($data, 'Checklist retrieved successfully');
    }


    /**
     * Submit a checklist: create Inspection + Answers
     * POST /v1/checklists/{checklist}/submit
     */
    public function submit(Request $request, Checklist $checklist)
    {
        $user = Auth::user();
        $employeeId = $user->employee?->id;
        if (!$employeeId) {
            return ResponseFormatter::error('Employee not found', 404);
        }

        $data = $request->validate([
            'answers' => 'required|array|min:1',
            'answers.*.question_id' => 'required|integer|exists:questions,id',
            'answers.*.answer' => 'nullable|string',
            'answers.*.note' => 'nullable|string',
            'remarks' => 'nullable|string',
            'location_id' => 'nullable|integer|exists:branches,id',
            'model_type' => 'nullable|string',
            'model_id' => 'nullable|integer',
        ]);

        try {
            $result = DB::transaction(function () use ($data, $checklist, $user, $employeeId) {
                $inspection = Inspection::create([
                    'checklist_id' => $checklist->id,
                    'inspection_number' => 'INS-' . now()->format('YmdHis') . '-' . $user->id,
                    'submit_date' => now(),
                    'submitted_by' => $user->id,
                    'status' => 'submitted',
                    'approved_by' => null,
                    'approved_date' => null,
                    'remarks' => $data['remarks'] ?? null,
                    'location_id' => $data['location_id'] ?? null,
                    'model_type' => $data['model_type'] ?? null,
                    'model_id' => $data['model_id'] ?? null,
                ]);

                foreach ($data['answers'] as $ans) {
                    Answer::create([
                        'question_id' => $ans['question_id'],
                        'employee_id' => $employeeId,
                        'inspection_id' => $inspection->id,
                        'answer' => $ans['answer'] ?? null,
                        'note' => $ans['note'] ?? null,
                    ]);
                }

                return $inspection->load(['answers']);
            });
        } catch (\Throwable $e) {
            return ResponseFormatter::error('Failed to submit checklist: ' . $e->getMessage(), 500);
        }

        // After saved: push notifications to Supervisors in the submitter's department
        try {
            $deptId = $user->employee ? $user->employee->department_id : null;
            if (!$deptId && isset($checklist->department_id)) {
                $deptId = $checklist->department_id;
            }

            if ($deptId) {
                $supervisorIds = User::role('Supervisor')
                    ->whereHas('employee', function ($q) use ($deptId) {
                        $q->where('department_id', $deptId);
                    })
                    ->pluck('id')
                    ->map(function ($v) {
                        return (string) $v;
                    })
                    ->toArray();

                if (!empty($supervisorIds)) {
                    $title = 'Checklist Disubmit';
                    $msg = ($user ? $user->name : 'Karyawan') . ' menyelesaikan checklist ' . ($checklist->name ?? '#' . $checklist->id) . ' pada ' . now()->format('d M Y H:i');
                    $this->sendPushOneSignal($supervisorIds, $title, $msg, [
                        'type' => 'inspection',
                        'inspection_id' => $result->id,
                        'checklist_id' => $checklist->id,
                        'department_id' => $deptId,
                    ]);

                    // Create notifications table entries for supervisors
                    $now = now();
                    $notifRows = array_map(function ($uid) use ($msg, $now) {
                        return [
                            'user_id' => (int) $uid,
                            'pesan' => $msg,
                            'status' => 'unread',
                            'created_at' => $now,
                            'updated_at' => $now,
                        ];
                    }, $supervisorIds);

                    if (!empty($notifRows)) {
                        DB::table('notifications')->insert($notifRows);
                    }
                }
            }

            // Create activity log entry
            try {
                DB::table('log_activies')->insert([
                    'users_id' => $user->id,
                    'model_type' => 'App\\Models\\Inspection',
                    'model_id' => $result->id,
                    'description' => 'Submit checklist #' . ($checklist->id) . ' - Inspection #' . $result->id,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            } catch (\Throwable $e) {
                // ignore log failures
            }
        } catch (\Throwable $e) {
            // Do not block response on notification failure
        }

        return ResponseFormatter::success($result, 'Checklist submitted successfully');
    }
    /**
     * Store answer for a specific question
     * POST /v1/checklists/answer
     */
    public function storeAnswer(Request $request)
    {
        $user = Auth::user();
        $employeeId = $user->employee?->id;
        if (!$employeeId) {
            return ResponseFormatter::error('Employee not found', 404);
        }

        $data = $request->validate([
            'checklist_id' => 'required|integer|exists:checklists,id',
            'question_id' => 'required|integer|exists:questions,id',
            'answer' => 'nullable|string',
            'note' => 'nullable|string',
            'inspection_id' => 'nullable|integer|exists:inspections,id',
        ]);

        try {
            $answer = Answer::updateOrCreate([
                'question_id' => $data['question_id'],
                'employee_id' => $employeeId,
                'inspection_id' => $data['inspection_id'] ?? null,
            ], [
                'answer' => $data['answer'] ?? null,
                'note' => $data['note'] ?? null,
            ]);

            return ResponseFormatter::success($answer, 'Answer stored successfully');
        } catch (\Throwable $e) {
            return ResponseFormatter::error('Failed to store answer: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Store attachment for a specific question answer
     * POST /v1/checklists/attachment
     */
    public function storeAttachment(Request $request)
    {
        $user = Auth::user();
        $employeeId = $user->employee?->id;
        if (!$employeeId) {
            return ResponseFormatter::error('Employee not found', 404);
        }

        $data = $request->validate([
            'checklist_id' => 'required|integer|exists:checklists,id',
            'question_id' => 'required|integer|exists:questions,id',
            'attachment' => 'required|file|max:10240', // 10MB max
            'inspection_id' => 'nullable|integer|exists:inspections,id',
        ]);

        try {
            $file = $request->file('attachment');
            $path = $file->store('attachments/answers', 'public');

            $answer = Answer::updateOrCreate([
                'question_id' => $data['question_id'],
                'employee_id' => $employeeId,
                'inspection_id' => $data['inspection_id'] ?? null,
            ], [
                'attachment' => $path,
            ]);

            return ResponseFormatter::success($answer, 'Attachment stored successfully');
        } catch (\Throwable $e) {
            return ResponseFormatter::error('Failed to store attachment: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Store note for a specific question answer
     * POST /v1/checklists/note
     */
    public function storeNote(Request $request)
    {
        $user = Auth::user();
        $employeeId = $user->employee?->id;
        if (!$employeeId) {
            return ResponseFormatter::error('Employee not found', 404);
        }

        $data = $request->validate([
            'checklist_id' => 'required|integer|exists:checklists,id',
            'question_id' => 'required|integer|exists:questions,id',
            'note' => 'required|string',
            'inspection_id' => 'nullable|integer|exists:inspections,id',
        ]);

        try {
            $answer = Answer::updateOrCreate([
                'question_id' => $data,
                'employee_id' => $employeeId,
                'inspection_id' => $data['inspection_id'] ?? null,
            ], [
                'note' => $data['note'],
            ]);

            return ResponseFormatter::success($answer, 'Note stored successfully');
        } catch (\Throwable $e) {
            return ResponseFormatter::error('Failed to store note: ' . $e->getMessage(), 500);
        }
    }

    // public function bulkSave(Request $request, Checklist $checklist)
    // {
    //     $user = Auth::user();
    //     $employeeId = $user->employee?->id;
    //     if (!$employeeId) {
    //         return ResponseFormatter::error('Employee not found', 404);
    //     }

    //     // fetch daftar karyawan sesuai karyawan yang submit
    //     $userDept = User::where('dep')

    //     $data = $request->validate([
    //         'answers' => 'required|array|min:1',
    //         'answers.*.question_id' => 'required|integer|exists:questions,id',
    //         'answers.*.answer' => 'nullable|string',
    //         'answers.*.note' => 'nullable|string',
    //         'remarks' => 'nullable|string',
    //         'location_id' => 'nullable|integer|exists:branches,id',
    //         'model_type' => 'nullable|string',
    //         'model_id' => 'nullable|integer',
    //     ]);

    //     try {
    //         $result = DB::transaction(function () use ($data, $checklist, $user, $employeeId) {
    //             $inspection = Inspection::create([
    //                 'checklist_id' => $checklist->id,
    //                 'inspection_number' => 'INS-' . now()->format('YmdHis') . '-' . $user->id,
    //                 'submit_date' => now(),
    //                 'submitted_by' => $user->id,
    //                 'status' => 'submitted',
    //                 'approved_by' => null,
    //                 'approved_date' => null,
    //                 'remarks' => $data['remarks'] ?? null,
    //                 'location_id' => $data['location_id'] ?? null,
    //                 'model_type' => $data['model_type'] ?? null,
    //                 'model_id' => $data['model_id'] ?? null,
    //             ]);

    //             foreach ($data['answers'] as $ans) {
    //                 Answer::create([
    //                     'question_id' => $ans['question_id'],
    //                     'employee_id' => $employeeId,
    //                     'inspection_id' => $inspection->id,
    //                     'answer' => $ans['answer'] ?? null,
    //                     'note' => $ans['note'] ?? null,
    //                 ]);
    //             }

    //             return $inspection->load(['answers']);
    //         });
    //     } catch (\Throwable $e) {
    //         return ResponseFormatter::error('Failed to submit checklist: ' . $e->getMessage(), 500);
    //     }

    //     // After saved: push notifications to Supervisors in the submitter's department
    //     try {
    //         $deptId = $user->employee ? $user->employee->department_id : null;
    //         if (!$deptId && isset($checklist->department_id)) {
    //             $deptId = $checklist->department_id;
    //         }

    //         if ($deptId) {
    //             $supervisorIds = User::role('Supervisor')
    //                 ->whereHas('employee', function ($q) use ($deptId) {
    //                     $q->where('department_id', $deptId);
    //                 })
    //                 ->pluck('id')
    //                 ->map(function ($v) {
    //                     return (string) $v;
    //                 })
    //                 ->toArray();

    //             if (!empty($supervisorIds)) {
    //                 $title = 'Checklist Disubmit';
    //                 $msg = ($user ? $user->name : 'Karyawan') . ' menyelesaikan checklist ' . ($checklist->name ?? '#' . $checklist->id) . ' pada ' . now()->format('d M Y H:i');
    //                 $this->sendPushOneSignal($supervisorIds, $title, $msg, [
    //                     'type' => 'inspection',
    //                     'inspection_id' => $result->id,
    //                     'checklist_id' => $checklist->id,
    //                     'department_id' => $deptId,
    //                 ]);

    //                 // Create notifications table entries for supervisors
    //                 $now = now();
    //                 $notifRows = array_map(function ($uid) use ($msg, $now) {
    //                     return [
    //                         'user_id' => (int) $uid,
    //                         'pesan' => $msg,
    //                         'status' => 'unread',
    //                         'created_at' => $now,
    //                         'updated_at' => $now,
    //                     ];
    //                 }, $supervisorIds);

    //                 if (!empty($notifRows)) {
    //                     DB::table('notifications')->insert($notifRows);
    //                 }
    //             }
    //         }

    //         // Create activity log entry
    //         try {
    //             DB::table('log_activies')->insert([
    //                 'users_id' => $user->id,
    //                 'model_type' => 'App\\Models\\Inspection',
    //                 'model_id' => $result->id,
    //                 'description' => 'Submit checklist #' . ($checklist->id) . ' - Inspection #' . $result->id,
    //                 'created_at' => now(),
    //                 'updated_at' => now(),
    //             ]);
    //         } catch (\Throwable $e) {
    //             // ignore log failures
    //         }
    //     } catch (\Throwable $e) {
    //         // Do not block response on notification failure
    //     }

    //     return ResponseFormatter::success($result, 'Checklist submitted successfully');
    // }

    /**
     * Send push notification using OneSignal to external user ids array
     */
    private function sendPushOneSignal(array $externalUserIds, string $title, string $message, array $data = []): void
    {
        $appId = config('services.onesignal.app_id', env('ONESIGNAL_APP_ID'));
        $apiKey = config('services.onesignal.rest_api_key', env('ONESIGNAL_REST_API_KEY'));
        if (!$appId || !$apiKey || empty($externalUserIds)) return;

        $payload = [
            'app_id' => $appId,
            'include_external_user_ids' => array_values($externalUserIds),
            'headings' => ['en' => $title, 'id' => $title],
            'contents' => ['en' => $message, 'id' => $message],
            'data' => $data,
        ];

        Http::withHeaders([
            'Authorization' => 'Basic ' . $apiKey,
            'Content-Type' => 'application/json',
        ])->post('https://onesignal.com/api/v1/notifications', $payload);
    }


    public function staff(Checklist $checklist, Request $request)
    {
        try {
            $inspectionId = $request->input('inspection_id');

            $currentEmployeeId = Auth::user()->employee?->id;

            $employees = $checklist->employees()->where('employee_id', '!=', $currentEmployeeId)
                ->withCount([
                    'answers as total_answered' => function ($query) use ($inspectionId) {
                        $query->where('inspection_id', $inspectionId);
                    },
                ])
                ->get()
                ->map(function ($employee) use ($checklist) {
                    $totalQuestions = $checklist->questions()->count();

                    return [
                        'id'              => (int)$employee->id,
                        'name'            => $employee->name,
                        'total_questions' => (int)$totalQuestions,
                        'answered'        => (int)$employee->total_answered,
                        'checklist'       => new ChecklistResource($checklist),
                    ];
                });

            // Create activity log entry
            DB::table('log_activies')->insert([
                'users_id' => Auth::id(),
                'model_type' => 'App\\Models\\Checklist',
                'model_id' => $checklist->id,
                'description' => 'View staff list for checklist #' . $checklist->id . ' (inspection: ' . ($inspectionId ?? 'N/A') . ')',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            return ResponseFormatter::success($employees, 'Checklist retrieved other staff successfully');
        } catch (\Throwable $e) {
            return ResponseFormatter::error('Failed to retrieve staff: ' . $e->getMessage(), 500);
        }
    }
}
