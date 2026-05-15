<?php

namespace App\Http\Controllers;

use App\Models\Inspection;
use App\Models\Checklist;
use App\Models\Branch;
use App\Models\User;
use App\Models\Employee;
use App\Models\EmployeeDocument;
use App\Models\Answer;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class InspectionController extends Controller
{
    public function index(Request $request)
    {
        $q = trim((string) $request->input('q', ''));
        $status = $request->input('status');
        $checklistId = $request->input('checklist_id');
        $locationId = $request->input('location_id');
        $submittedBy = $request->input('submitted_by');
        $dateFrom = $request->input('date_from');
        $dateTo = $request->input('date_to');
        $perPage = (int) $request->input('per_page', 10);
        $groupBy = $request->input('group_by');
        $isSuperadmin = Auth::user()->hasRole("Superadmin");

        $inspections = Inspection::query()
            ->with([
                'checklist.category',
                'createdBy',
                'submittedBy',
                'approvedBy',
                'location',
                'answers',
                'model'
            ])
            ->when($q !== '', function ($qrb) use ($q) {
                $qrb->where(function ($w) use ($q) {
                    $w->where('inspection_number', 'like', "%{$q}%")
                        ->orWhere('remarks', 'like', "%{$q}%");
                });
            })
            ->when($status, function ($qb) use ($status) {
                $qb->where('status', $status);
            })
            ->when(!$isSuperadmin && Auth::user()->employee->branch_id != 2, function ($qb) {
                $qb->whereHas('checklist', function ($q) {
                    $q->whereHas('departments', function ($q) {
                        $q->where('branch_id', Auth::user()->employee->branch_id);
                    });
                });
            })
            ->when($checklistId, function ($qb) use ($checklistId) {
                $qb->where('checklist_id', $checklistId);
            })
            ->when($locationId, function ($qb) use ($locationId) {
                $qb->where('location_id', $locationId);
            })
            ->when($submittedBy, function ($qb) use ($submittedBy) {
                $qb->where('submitted_by', $submittedBy);
            })
            ->when($dateFrom, function ($qb) use ($dateFrom) {
                $qb->whereDate('submit_date', '>=', $dateFrom);
            })
            ->when($dateTo, function ($qb) use ($dateTo) {
                $qb->whereDate('submit_date', '<=', $dateTo);
            })
            ->orderByDesc('submit_date')
            ->paginate($perPage)
            ->withQueryString()
            ->through(function ($row) {
                // expose compact user objects for convenience
                $row->submitted_by_user = $row->submittedBy ? ['id' => $row->submittedBy->id, 'name' => $row->submittedBy->name] : null;
                $row->approved_by_user = $row->approvedBy ? ['id' => $row->approvedBy->id, 'name' => $row->approvedBy->name] : null;
                // translate status label to Indonesian (capitalized)
                $row->status = $this->statusLabelId($row->status);

                // Calculate condition percentage (good answers)
                $totalAnswers = $row->answers->count();
                if ($totalAnswers > 0) {
                    $goodAnswers = $row->answers->filter(function ($ans) {
                        $answer = strtolower(trim($ans->answer ?? ''));
                        return in_array($answer, ['baik', 'true', 'ya', 'yes', '1', 'ok']);
                    })->count();
                    $row->condition_percentage = round(($goodAnswers / $totalAnswers) * 100, 1);
                } else {
                    $row->condition_percentage = 0;
                }

                // Add odometer data for vehicle inspections
                $row->odometer_data = null;
                if ($row->model_type === 'App\\Models\\Vehicle' && $row->model) {
                    $latestOdometer = \App\Models\VehicleHistoryOdometer::where('vehicle_id', $row->model->id)
                        ->where('tanggal', '<=', $row->submit_date ?? now())
                        ->latest('tanggal')
                        ->first();

                    if ($latestOdometer) {
                        $row->odometer_data = [
                            'last_km' => number_format($latestOdometer->last_km, 0, ',', '.'),
                            'current_km' => number_format($latestOdometer->current_km, 0, ',', '.'),
                            'tanggal' => $latestOdometer->tanggal ? \Carbon\Carbon::parse($latestOdometer->tanggal)->format('d-m-Y') : '-',
                        ];
                    }
                }

                return $row;
            });
        // dd($inspections);



        return Inertia::render('Admin/Inspection/Index', [
            'inspections' => $inspections,
            'filters' => [
                'q' => $q,
                'status' => $status,
                'checklist_id' => $checklistId,
                'location_id' => $locationId,
                'submitted_by' => $submittedBy,
                'date_from' => $dateFrom,
                'date_to' => $dateTo,
                'per_page' => $perPage,
            ],
            'group_by' => $groupBy,
            'branches' => Branch::select('id', 'name')->orderBy('name')->get(),
            'statuses' => ['draft', 'on_progress', 'submitted', 'approved', 'rejected'],
            'checklists' => Checklist::select('id', 'name')->orderBy('name')->get(),
            'users' => User::select('id', 'name')->orderBy('name')->get(),
        ]);
    }

    public function show($id)
    {
        $inspection = Inspection::with([
            'checklist.category',
            'checklist.department.branch',
            'createdBy',
            'submittedBy',
            'approvedBy',
            'location',
            'answers.question.category',
            'answers.employee',
            'model.type'
        ])->findOrFail($id);

        // If this is an AJAX request for Quick Look, return JSON
        if (request()->wantsJson() || request()->header('X-Quick-Look')) {
            $questions = $inspection->answers->map(function ($ans) {
                $q = $ans->question;
                return [
                    'title' => $q ? ($q->title ?? $q->question ?? '-') : '-',
                    'answer' => $ans->answer,
                    'note' => $ans->note,
                ];
            });

            return response()->json([
                'questions' => $questions,
                'inspection' => [
                    'inspection_number' => $inspection->inspection_number,
                    'status' => $this->statusLabelId($inspection->status),
                    'submit_date' => $inspection->submit_date,
                    'location' => $inspection->location,
                    'condition_percentage' => 0, // Calculate if needed
                ]
            ]);
        }

        // Determine inspection type based on model_type
        $inspectionType = '-';
        $inspectionTarget = '-';
        if ($inspection->model_type && $inspection->model) {
            if ($inspection->model_type === 'App\\Models\\Vehicle') {
                $inspectionType = 'Kendaraan';
                $inspectionTarget = $inspection->model->license_plate ?? $inspection->model->name ?? '-';
            } elseif ($inspection->model_type === 'App\\Models\\Employee') {
                $inspectionType = 'Karyawan';
                $inspectionTarget = $inspection->model->name ?? '-';
            } else {
                $inspectionType = class_basename($inspection->model_type);
                $inspectionTarget = $inspection->model->name ?? $inspection->model->id ?? '-';
            }
        }

        // Shape data for UI tabs
        $general = [
            'inspection_number' => $inspection->inspection_number,
            'checklist_name' => optional($inspection->checklist)->name,
            'checklist_type' => optional($inspection->checklist)->type,
            'sop_code' => optional($inspection->checklist)->sop_code,
            'category' => optional(optional($inspection->checklist)->category)->name,
            'department' => optional(optional($inspection->checklist)->department)->name,
            'branch' => optional(optional(optional($inspection->checklist)->department)->branch)->name,
            'created_at' => $inspection->created_at ? $inspection->created_at->toDateTimeString() : null,
            'submit_date' => $inspection->submit_date ? $inspection->submit_date->toDateTimeString() : null,
            'created_by' => optional($inspection->createdBy)->name,
            'submitted_by' => optional($inspection->submittedBy)->name,
            'status' => $this->statusLabelId($inspection->status),
            'approved_by' => optional($inspection->approvedBy)->name,
            'approved_date' => $inspection->approved_date ? $inspection->approved_date->locale('id')->translatedFormat('d M Y H:i') : null,
            'remarks' => $inspection->remarks,
            'location' => optional($inspection->location)->name,
            'inspection_type' => $inspectionType,
            'inspection_target' => $inspectionTarget,
        ];

        // Ambil tanda tangan untuk export (Pengaju & Penyetuju)
        $submitterSignatureUrl = null;
        if ($inspection->submittedBy) {
            $submitterEmployee = Employee::where('user_id', $inspection->submittedBy->id)->first();
            if ($submitterEmployee) {
                $doc = EmployeeDocument::where('employee_id', $submitterEmployee->id)
                    ->whereIn('title', ['Tanda Tangan', 'Signature', 'TTD'])
                    ->latest('id')
                    ->first();
                if ($doc && $doc->file_path) {
                    $submitterSignatureUrl = filter_var($doc->file_path, FILTER_VALIDATE_URL)
                        ? $doc->file_path
                        : asset('storage/' . ltrim($doc->file_path, '/'));
                }
            }
        }

        $approverSignatureUrl = null;
        if ($inspection->approvedBy) {
            $approverEmployee = Employee::where('user_id', $inspection->approvedBy->id)->first();
            if ($approverEmployee) {
                $doc = EmployeeDocument::where('employee_id', $approverEmployee->id)
                    ->whereIn('title', ['Tanda Tangan', 'Signature', 'TTD'])
                    ->latest('id')
                    ->first();
                if ($doc && $doc->file_path) {
                    $approverSignatureUrl = filter_var($doc->file_path, FILTER_VALIDATE_URL)
                        ? $doc->file_path
                        : asset('storage/' . ltrim($doc->file_path, '/'));
                }
            }
        }

        // Ambil tanda tangan untuk export (Pengaju & Penyetuju)
        $submitterSignatureUrl = null;
        if ($inspection->submittedBy) {
            $submitterEmployee = Employee::where('user_id', $inspection->submittedBy->id)->first();
            if ($submitterEmployee) {
                $doc = EmployeeDocument::where('employee_id', $submitterEmployee->id)
                    ->whereIn('title', ['Tanda Tangan', 'Signature', 'TTD'])
                    ->latest('id')
                    ->first();
                if ($doc && $doc->file_path) {
                    $submitterSignatureUrl = filter_var($doc->file_path, FILTER_VALIDATE_URL)
                        ? $doc->file_path
                        : asset('storage/' . ltrim($doc->file_path, '/'));
                }
            }
        }

        $approverSignatureUrl = null;
        if ($inspection->approvedBy) {
            $approverEmployee = Employee::where('user_id', $inspection->approvedBy->id)->first();
            if ($approverEmployee) {
                $doc = EmployeeDocument::where('employee_id', $approverEmployee->id)
                    ->whereIn('title', ['Tanda Tangan', 'Signature', 'TTD'])
                    ->latest('id')
                    ->first();
                if ($doc && $doc->file_path) {
                    $approverSignatureUrl = filter_var($doc->file_path, FILTER_VALIDATE_URL)
                        ? $doc->file_path
                        : asset('storage/' . ltrim($doc->file_path, '/'));
                }
            }
        }

        // Ambil tanda tangan untuk export (Pengaju & Penyetuju)
        $submitterSignatureUrl = null;
        if ($inspection->submittedBy) {
            $submitterEmployee = Employee::where('user_id', $inspection->submittedBy->id)->first();
            if ($submitterEmployee) {
                $doc = EmployeeDocument::where('employee_id', $submitterEmployee->id)
                    ->whereIn('title', ['Tanda Tangan', 'Signature', 'TTD'])
                    ->latest('id')
                    ->first();
                if ($doc && $doc->file_path) {
                    $submitterSignatureUrl = filter_var($doc->file_path, FILTER_VALIDATE_URL)
                        ? $doc->file_path
                        : asset('storage/' . ltrim($doc->file_path, '/'));
                }
            }
        }

        $approverSignatureUrl = null;
        if ($inspection->approvedBy) {
            $approverEmployee = Employee::where('user_id', $inspection->approvedBy->id)->first();
            if ($approverEmployee) {
                $doc = EmployeeDocument::where('employee_id', $approverEmployee->id)
                    ->whereIn('title', ['Tanda Tangan', 'Signature', 'TTD'])
                    ->latest('id')
                    ->first();
                if ($doc && $doc->file_path) {
                    $approverSignatureUrl = filter_var($doc->file_path, FILTER_VALIDATE_URL)
                        ? $doc->file_path
                        : asset('storage/' . ltrim($doc->file_path, '/'));
                }
            }
        }
        // Group questions/answers by question category name if exists
        $questions = $inspection->answers->map(function ($ans) {
            $q = $ans->question;
            $cat = $q ? $q->category : null;

            // Get attachment URL if exists
            $attachmentUrl = null;
            if ($ans->attachment) {
                // Check if it's already a full URL
                if (filter_var($ans->attachment, FILTER_VALIDATE_URL)) {
                    $attachmentUrl = $ans->attachment;
                } else {
                    // Build URL from storage path
                    $attachmentUrl = asset('storage/' . $ans->attachment);
                }
            }

            return [
                'question_id' => $q ? $q->id : null,
                'title' => $q ? ($q->question ?? '-') : '-',
                'category' => $cat ? $cat->name : null,
                'answer' => $ans->answer ?? '-',
                'note' => $ans->note ?? null,
                'attachment' => $attachmentUrl,
                'guidance' => $q ? ($q->guidance ?? '-') : '-',
                // Add employee info if exists (for vehicle inspections)
                'employee_id' => $ans->employee_id ?? null,
                'employee_name' => $ans->employee ? $ans->employee->name : null,
            ];
        });


        // Simple activity list (you can replace with real audit logs later)
        $activities = [
            [
                'at' => $inspection->created_at ? $inspection->created_at->toDateTimeString() : null,
                'action' => 'Draft dibuat',
                'by' => optional($inspection->createdBy)->name,
                'notes' => null,
            ],
            [
                'at' => $inspection->submit_date ? $inspection->submit_date->toDateTimeString() : null,
                'action' => 'Diajukan',
                'by' => optional($inspection->submittedBy)->name,
                'notes' => $inspection->remarks,
            ],
            [
                'at' => $inspection->approved_date ? $inspection->approved_date->toDateTimeString() : null,
                'action' => ucfirst($inspection->status),
                'by' => optional($inspection->approvedBy)->name,
                'notes' => null,
            ],
        ];

        // Ambil tanda tangan karyawan (EmployeeDocument dengan title Tanda Tangan/Signature/TTD)
        $submitterSignatureUrl = null;
        if ($inspection->submittedBy) {
            $submitterEmployee = Employee::where('user_id', $inspection->submittedBy->id)->first();
            if ($submitterEmployee) {
                $doc = EmployeeDocument::where('employee_id', $submitterEmployee->id)
                    ->whereIn('title', ['Tanda Tangan', 'Signature', 'TTD'])
                    ->latest('id')
                    ->first();
                if ($doc && $doc->file_path) {
                    $submitterSignatureUrl = filter_var($doc->file_path, FILTER_VALIDATE_URL)
                        ? $doc->file_path
                        : asset('storage/' . ltrim($doc->file_path, '/'));
                }
            }
        }

        $approverSignatureUrl = null;
        if ($inspection->approvedBy) {
            $approverEmployee = Employee::where('user_id', $inspection->approvedBy->id)->first();
            if ($approverEmployee) {
                $doc = EmployeeDocument::where('employee_id', $approverEmployee->id)
                    ->whereIn('title', ['Tanda Tangan', 'Signature', 'TTD'])
                    ->latest('id')
                    ->first();
                if ($doc && $doc->file_path) {
                    $approverSignatureUrl = filter_var($doc->file_path, FILTER_VALIDATE_URL)
                        ? $doc->file_path
                        : asset('storage/' . ltrim($doc->file_path, '/'));
                }
            }
        }

        // Simple approvals table (PIC + tanda tangan)
        $approvals = [
            [
                'step' => 'Pengaju',
                'pic' => optional($inspection->submittedBy)->name,
                'status' => $inspection->status === 'draft' ? 'draft' : 'submitted',
                'date' => $inspection->submit_date ? $inspection->submit_date->toDateTimeString() : null,
                'signature_url' => $submitterSignatureUrl,
            ],
            [
                'step' => 'Penyetuju',
                'pic' => optional($inspection->approvedBy)->name,
                'status' => $inspection->status,
                'date' => $inspection->approved_date ? $inspection->approved_date->toDateTimeString() : null,
                'signature_url' => $approverSignatureUrl,
            ],
        ];

        // Get respondents (users who filled the questionnaire) with their signatures
        $respondents = [];
        $employeeIds = $inspection->answers->pluck('employee_id')->unique()->filter();

        foreach ($employeeIds as $employeeId) {
            $employee = Employee::with('user')->find($employeeId);
            if ($employee) {
                // Get signature from EmployeeDocument
                $signatureUrl = null;
                $doc = EmployeeDocument::where('employee_id', $employee->id)
                    ->whereIn('title', ['Tanda Tangan', 'Signature', 'TTD'])
                    ->latest('id')
                    ->first();

                if ($doc && $doc->file_path) {
                    $signatureUrl = filter_var($doc->file_path, FILTER_VALIDATE_URL)
                        ? $doc->file_path
                        : asset('storage/' . ltrim($doc->file_path, '/'));
                }

                // Get created_at from the earliest answer by this employee (when user first filled the questionnaire)
                $earliestAnswer = Answer::where('inspection_id', $inspection->id)
                    ->where('employee_id', $employeeId)
                    ->orderBy('created_at', 'asc')
                    ->first();

                $createdAt = null;
                if ($earliestAnswer && $earliestAnswer->created_at) {
                    $createdAt = $earliestAnswer->created_at->toDateTimeString();
                }

                $respondents[] = [
                    'employee_id' => $employee->id,
                    'employee_name' => $employee->name,
                    'user_id' => $employee->user ? $employee->user->id : null,
                    'user_name' => $employee->user ? $employee->user->name : null,
                    'signature_url' => $signatureUrl,
                    'created_at' => Carbon::parse($createdAt)->locale('id')->translatedFormat('d M Y H:i'),
                ];
            }
        }

        // dd($questions);

        return Inertia::render('Admin/Inspection/Show', [
            'inspection' => $inspection,
            'general' => $general,
            'questions' => $questions,
            'activities' => $activities,
            'approvals' => $approvals,
            'respondents' => $respondents,
        ]);
    }

    public function export($id)
    {
        $inspection = Inspection::with([
            'checklist.category',
            'checklist.department.branch',
            'createdBy',
            'submittedBy',
            'approvedBy',
            'location',
            'answers.question.category',
            'answers.employee',
            'model'
        ])->findOrFail($id);

        $inspectionType = '-';
        $inspectionTarget = '-';
        if ($inspection->model_type && $inspection->model) {
            if ($inspection->model_type === 'App\\Models\\Vehicle') {
                $inspectionType = 'Kendaraan';
                $inspectionTarget = $inspection->model->license_plate ?? $inspection->model->name ?? '-';
            } elseif ($inspection->model_type === 'App\\Models\\Employee') {
                $inspectionType = 'Karyawan';
                $inspectionTarget = $inspection->model->name ?? '-';
            } else {
                $inspectionType = class_basename($inspection->model_type);
                $inspectionTarget = $inspection->model->name ?? $inspection->model->id ?? '-';
            }
        }

        $general = [
            'inspection_number' => $inspection->inspection_number,
            'checklist_name' => optional($inspection->checklist)->name,
            'checklist_type' => optional($inspection->checklist)->type,
            'sop_code' => optional($inspection->checklist)->sop_code,
            'category' => optional(optional($inspection->checklist)->category)->name,
            'department' => optional(optional($inspection->checklist)->department)->name,
            'branch' => optional(optional(optional($inspection->checklist)->department)->branch)->name,
            'created_at' => $inspection->created_at ? $inspection->created_at->toDateTimeString() : null,
            'submit_date' => $inspection->submit_date ? $inspection->submit_date->toDateTimeString() : null,
            'created_by' => optional($inspection->createdBy)->name,
            'submitted_by' => optional($inspection->submittedBy)->name,
            'status' => $this->statusLabelId($inspection->status),
            'approved_by' => optional($inspection->approvedBy)->name,
            'approved_date' => $inspection->approved_date ? $inspection->approved_date->locale('id')->translatedFormat('d M Y H:i') : null,
            'remarks' => $inspection->remarks,
            'location' => optional($inspection->location)->name,
            'inspection_type' => $inspectionType,
            'inspection_target' => $inspectionTarget,
        ];

        // Get submitter signature
        $submitterSignatureUrl = null;
        if ($inspection->submittedBy) {
            $submitterEmployee = Employee::where('user_id', $inspection->submittedBy->id)->first();
            if ($submitterEmployee) {
                $doc = EmployeeDocument::where('employee_id', $submitterEmployee->id)
                    ->whereIn('title', ['Tanda Tangan', 'Signature', 'TTD'])
                    ->latest('id')
                    ->first();
                if ($doc && $doc->file_path) {
                    $submitterSignatureUrl = filter_var($doc->file_path, FILTER_VALIDATE_URL)
                        ? $doc->file_path
                        : asset('storage/' . ltrim($doc->file_path, '/'));
                }
            }
        }

        // Get approver signature
        $approverSignatureUrl = null;
        if ($inspection->approvedBy) {
            $approverEmployee = Employee::where('user_id', $inspection->approvedBy->id)->first();
            if ($approverEmployee) {
                $doc = EmployeeDocument::where('employee_id', $approverEmployee->id)
                    ->whereIn('title', ['Tanda Tangan', 'Signature', 'TTD'])
                    ->latest('id')
                    ->first();
                if ($doc && $doc->file_path) {
                    $approverSignatureUrl = filter_var($doc->file_path, FILTER_VALIDATE_URL)
                        ? $doc->file_path
                        : asset('storage/' . ltrim($doc->file_path, '/'));
                }
            }
        }

        // Determine if this is a multiple checklist (many people)
        $rawType = optional($inspection->checklist)->type;
        $type = $rawType ? strtolower(trim($rawType)) : '';
        $typeIndicatesMultiple = str_contains($type, 'banyak');
        $hasEmployeeAnswers = $inspection->answers->contains(function ($ans) {
            return !is_null($ans->employee_id);
        });

        $isMultiple = $typeIndicatesMultiple || $hasEmployeeAnswers;

        $employeeGroups = [];
        $employees = [];
        $questionsByCategory = [];

        if ($isMultiple) {
            // Build structures: employees list and questions by category with answers per employee
            foreach ($inspection->answers as $ans) {
                $q = $ans->question;
                $employeeName = $ans->employee->name ?? optional($inspection->submittedBy)->name ?? 'Karyawan';
                $categoryName = $q && $q->category ? $q->category->name : 'Umum';

                // Collect unique employees
                if (!in_array($employeeName, $employees, true)) {
                    $employees[] = $employeeName;
                }

                if (!isset($questionsByCategory[$categoryName])) {
                    $questionsByCategory[$categoryName] = [];
                }

                $questionKey = $q ? ($q->id ?? $q->question ?? $q->title) : null;
                if (!isset($questionsByCategory[$categoryName][$questionKey])) {
                    $questionsByCategory[$categoryName][$questionKey] = [
                        'title' => $q ? ($q->title ?? $q->question ?? '-') : '-',
                        'answers' => [],
                    ];
                }

                $questionsByCategory[$categoryName][$questionKey]['answers'][$employeeName] = $ans->answer ?? '-';
            }
        } else {
            // Build employee -> category -> questions structure (single person style)
            foreach ($inspection->answers as $ans) {
                $q = $ans->question;
                $employeeName = $ans->employee->name ?? optional($inspection->submittedBy)->name ?? 'Karyawan';
                $categoryName = $q && $q->category ? $q->category->name : 'Umum';

                if (!isset($employeeGroups[$employeeName])) {
                    $employeeGroups[$employeeName] = [];
                }
                if (!isset($employeeGroups[$employeeName][$categoryName])) {
                    $employeeGroups[$employeeName][$categoryName] = [];
                }

                $employeeGroups[$employeeName][$categoryName][] = [
                    'title' => $q ? ($q->title ?? $q->question ?? '-') : '-',
                    'answer' => $ans->answer ?? '-',
                    'note' => $ans->note ?? '-',
                ];
            }
        }

        return view('exports.inspection-report', [
            'general' => $general,
            'isMultiple' => $isMultiple,
            'employees' => $employees,
            'questionsByCategory' => $questionsByCategory,
            'employeeGroups' => $employeeGroups,
            'submitterSignatureUrl' => $submitterSignatureUrl,
            'approverSignatureUrl' => $approverSignatureUrl,
        ]);
    }

    /**
     * Trigger SPV reminder manually (for development/testing only)
     */
    public function sendSpvReminder($id)
    {
        // Only allow in local/dev environment
        if (!in_array(config('app.env'), ['local', 'development', 'dev'])) {
            abort(403, 'This feature is only available in development environment');
        }

        try {
            $inspection = Inspection::with(['submittedBy', 'checklist', 'model'])->findOrFail($id);

            if (!$inspection->submittedBy) {
                return response()->json([
                    'success' => false,
                    'message' => 'Inspection tidak memiliki submitted_by user',
                ], 400);
            }

            // Get checklist name and model name
            $checklistName = $inspection->checklist ? $inspection->checklist->name : 'Checklist';
            $modelName = '';
            if ($inspection->model) {
                $modelName = $inspection->model->name ?? $inspection->model->plate_number ?? '';
            }

            // Call NotificationService to send SPV reminder
            $notificationService = app(\App\Services\NotificationService::class);
            $notificationService->notifySupervisorsOnInspectionSubmit(
                $inspection->id,
                $inspection->submittedBy,
                [
                    'checklist_name' => $checklistName,
                    'inspection_number' => $inspection->inspection_number,
                    'model_type' => $inspection->model_type,
                    'model_name' => $modelName,
                ]
            );

            \Log::info('[InspectionController] Manual SPV reminder triggered for inspection', [
                'user_id' => auth()->id(),
                'inspection_id' => $inspection->id,
                'inspection_number' => $inspection->inspection_number,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Reminder ke SPV berhasil dikirim! Cek log untuk detail.',
            ]);
        } catch (\Exception $e) {
            \Log::error('[InspectionController] Manual SPV reminder failed for inspection', [
                'inspection_id' => $id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Gagal mengirim reminder: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function destroy(int $id)
    {
        try {
            $inspection = Inspection::findOrFail($id);
            $inspection->delete();

            return redirect()->route('inspections.index')->with('success', 'Inspeksi berhasil dihapus');
        } catch (\Exception $e) {
            return redirect()->route('inspections.index')->with('error', 'Gagal menghapus inspeksi: ' . $e->getMessage());
        }
    }

    /**
     * Get answers for Quick Look modal
     * GET /inspections/{id}/answers
     */
    public function answers($id)
    {
        $inspection = Inspection::with([
            'checklist',
            'answers.question',
            'location',
            'submittedBy'
        ])->findOrFail($id);

        // Calculate condition percentage
        $totalAnswers = $inspection->answers->count();
        $conditionPercentage = 0;
        if ($totalAnswers > 0) {
            $goodAnswers = $inspection->answers->filter(function ($ans) {
                $answer = strtolower(trim($ans->answer ?? ''));
                return in_array($answer, ['baik', 'true', 'ya', 'yes', '1', 'ok']);
            })->count();
            $conditionPercentage = round(($goodAnswers / $totalAnswers) * 100, 1);
        }

        $questions = $inspection->answers->map(function ($ans) {
            $q = $ans->question;
            return [
                'no' => $q ? $q->id : null,
                'pertanyaan' => $q ? ($q->title ?? $q->question ?? '-') : '-',
                'jawaban' => $ans->answer ?? '-',
                'catatan' => $ans->note,
            ];
        });

        return response()->json([
            'questions' => $questions,
            'inspection' => [
                'inspection_number' => $inspection->inspection_number,
                'checklist_name' => $inspection->checklist->name ?? '-',
                'status' => $this->statusLabelId($inspection->status),
                'submit_date' => $inspection->submit_date,
                'submitted_by' => $inspection->submittedBy->name ?? '-',
                'location' => $inspection->location->name ?? '-',
                'condition_percentage' => $conditionPercentage,
            ]
        ]);
    }

    private function statusLabelId(?string $status): string
    {
        $map = [
            'draft' => 'Belum Selesai',
            'on_progress' => 'Dalam Proses',
            'submitted' => 'Selesai',
            'approved' => 'Disetujui',
            'rejected' => 'Ditolak',
        ];
        if (!$status)
            return '-';
        $key = strtolower($status);
        return $map[$key] ?? ucfirst($key);
    }
}
