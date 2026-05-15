<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use App\Models\Branch;
use App\Models\Checklist;
use App\Models\ChecklistCategory;
use App\Models\Department;
use App\Models\Employee;
use App\Models\QuestionCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Gate;

class ChecklistController extends Controller
{
    public function indexPrime(Request $request)
    {
        abort_unless(Gate::allows('checklists.view'), 403, 'Anda tidak memiliki akses untuk melihat data checklist');

        // PrimeVue-style params
        $perPage = max(1, min((int) $request->get('per_page', 10), 100));
        $q = trim((string) $request->get('q', ''));
        $sortField = $request->get('sort_field', 'id');
        $sortOrder = (int) $request->get('sort_order', 1); // 1 asc, -1 desc

        // Whitelist sorting
        $allowedSorts = ['id', 'name', 'code_sop', 'status'];
        if (!in_array($sortField, $allowedSorts, true)) {
            $sortField = 'id';
        }
        $direction = $sortOrder === -1 ? 'desc' : 'asc';

        $query = Checklist::with([
            'category:id,name',
            'department:id,name,branch_id',
            'department.branch:id,name',
        ]);

        if ($q !== '') {
            $query->where(function ($w) use ($q) {
                $w->where('name', 'like', "%{$q}%")
                    ->orWhere('code_sop', 'like', "%{$q}%")
                    ->orWhere('description', 'like', "%{$q}%");
            });
        }

        $checklists = $query->orderBy($sortField, $direction)
            ->paginate($perPage)
            ->withQueryString()
            ->onEachSide(1)
            ->through(function ($c) {
                return [
                    'id' => $c->id,
                    'name' => $c->name,
                    'code_sop' => $c->code_sop,
                    // kirim status lower-case agar cocok dengan UI prime
                    'status' => strtolower((string) $c->status),
                    'category_id' => $c->category_id,
                    'category' => $c->category?->name,
                    'department' => $c->department?->name,
                    'branch' => optional($c->department?->branch)->name,
                    'description' => $c->description,
                ];
            });

        $categories = ChecklistCategory::orderBy('name')->get(['id', 'name']);
        $departments = Department::orderBy('name')->get(['id', 'name']);
        // $branches   = Branch::orderBy('name')->get(['id', 'name']); // jika dibutuhkan, aktifkan

        return Inertia::render('Admin/Checklists/IndexPrime', [
            'checklists' => $checklists,
            'categories' => $categories,
            'departments' => $departments,
            'filters' => [
                'q' => $q,
                'sort_field' => $sortField,
                'sort_order' => $sortOrder,
                'per_page' => $perPage,
            ],
        ]);
    }

    public function index(Request $request)
    {
        abort_unless(Gate::allows('checklists.view'), 403, 'Anda tidak memiliki akses untuk melihat data checklist');

        $sortBy = $request->get('sortBy', 'id');
        $sortDirection = $request->get('sortDirection', 'asc');
        $search = $request->input('search');
        $categoryId = $request->input('category_id');
        $perPage = $request->input('per_page', 10);

        $checklists = Checklist::with(['category', 'department.branch', 'questions.answers'])
            ->withCount('questions')
            ->when($search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%");
                });
            })
            ->when($categoryId, function ($query, $categoryId) {
                $query->where('category_id', $categoryId);
            })
            ->orderBy($sortBy, $sortDirection)
            ->paginate($perPage)
            ->withQueryString();

        $checklists->each(function ($item) {
            $totalQuestions = $item->questions_count; // dari withCount
            $answeredQuestions = $item->questions ? $item->questions->filter(function ($q) {
                return $q->answers && $q->answers->isNotEmpty();
            })->count() : 0;

            $item->progress = $totalQuestions > 0
                ? round(($answeredQuestions / $totalQuestions) * 100, 2)
                : 0;
        });

        // Get categories, branches, and departments for filter dropdown
        $categories = ChecklistCategory::orderBy('name')->get(['id', 'name']);
        $branches = Branch::orderBy('name')->get(['id', 'name']);
        $departments = Department::orderBy('name')->get(['id', 'name', 'branch_id']);

        return Inertia::render('Admin/Checklists/Index', [
            'checklists' => $checklists,
            'categories' => $categories,
            'branches' => $branches,
            'departments' => $departments,
            'sortBy' => $sortBy,
            'sortDirection' => $sortDirection,
            'filters' => [
                'search' => $search,
                'category_id' => $categoryId,
            ],
        ]);
    }

    public function create(Request $request)
    {
        abort_unless(Gate::allows('checklists.create'), 403, 'Anda tidak memiliki akses untuk menambah checklist');

        try {
            $categories = ChecklistCategory::orderBy('name')->get(['id', 'name']);
            $questionCategories = QuestionCategory::orderBy('name')->get(['id', 'name']);

            // Define types for the select dropdown
            $types = [
                ['id' => 'single', 'name' => 'Perorang'],
                ['id' => 'multiple', 'name' => 'Banyak Orang'],
            ];

            $departments = Department::with('branch:id,name')
                ->withCount('employees')
                ->orderBy('name')
                ->get(['id', 'name', 'branch_id']);

            return Inertia::render('Admin/Checklists/Create', [
                'categories' => $categories,
                'types' => $types,
                'questionCategories' => $questionCategories,
                'departments' => $departments,
            ]);
        } catch (\Exception $e) {
            Log::error('Error in checklist create: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Terjadi kesalahan saat memuat halaman.');
        }
    }

    public function edit(Request $request, Checklist $checklist)
    {
        abort_unless(Gate::allows('checklists.edit'), 403, 'Anda tidak memiliki akses untuk mengubah checklist');
        $checklist->load([
            'category:id,name',
            'department:id,name,branch_id',
            'department.branch:id,name',
            'questions.category:id,name',
            'questions.options:id,question_id,label,value',
        ]);
        $categories = ChecklistCategory::orderBy('name')->get(['id', 'name']);
        $branches = Branch::orderBy('name')->get(['id', 'name']);
        $questionCategories = QuestionCategory::orderBy('name')->get(['id', 'name']);

        // Load departments with branch relationship
        $departments = Department::with('branch:id,name')
            ->withCount('employees')
            ->orderBy('name')
            ->get(['id', 'name', 'branch_id']);

        // Get current checklist departments
        $checklistDepartments = $checklist->departments()->pluck('departments.id')->toArray();
        $questions = $checklist->questions->map(function ($q) {
            return [
                'id' => $q->id,
                'uid' => (string) $q->id,
                'title' => $q->question,
                'description' => $q->guidance,
                'answer_type' => $q->answer_type,
                'required' => (bool) $q->required,
                'category' => $q->category ? $q->category->id : null,
                'options' => $q->options->map(function ($o) {
                    return [
                        'id' => $o->id,
                        'uid' => (string) $o->id,
                        'label' => $o->label,
                        'value' => $o->value,
                    ];
                })->values(),
            ];
        })->values();

        $types = [
            ['id' => 'single', 'name' => 'Perorang'],
            ['id' => 'multiple', 'name' => 'Banyak Orang'],
        ];

        return Inertia::render('Admin/Checklists/Edit', [
            'checklist' => [
                'id' => $checklist->id,
                'name' => $checklist->name,
                'sop_code' => $checklist->sop_code ?? $checklist->code_sop ?? null,
                'category_id' => $checklist->category_id,
                'department_ids' => $checklistDepartments,
                'status' => $checklist->status,
                'description' => $checklist->description,
                'type' => $checklist->getAttributes()['type'] ?? 'single', // Get raw value, not accessor
                'durasi' => $checklist->durasi ?? 1,
                'count' => $checklist->count ?? 1,
            ],
            'categories' => $categories,
            'departments' => $departments,
            'branches' => $branches,
            'questionCategories' => $questionCategories,
            'types' => $types,
            'questions' => $questions,
        ]);
    }

    public function store(Request $request)
    {
        abort_unless(Gate::allows('checklists.create'), 403, 'Anda tidak memiliki akses untuk menambah checklist');

        try {
            $data = $request->validate([
                'name' => ['required', 'string', 'max:255'],
                'sop_code' => ['required', 'string', 'max:255'],
                'category_id' => ['required'],
                'description' => ['nullable', 'string'],
                'type' => ['required', 'in:single,multiple'],
                'status' => ['nullable', 'in:Draft,Active,Inactive'],
                'durasi' => ['required', 'integer', 'min:1'],
                'count' => ['required', 'integer', 'min:1'],
                'department_ids' => ['required', 'array'],
                'department_ids.*' => ['required', 'exists:departments,id'],
                'questions' => ['required', 'array', 'min:1'],
                'questions.*.title' => ['required', 'string'],
                'questions.*.description' => ['nullable', 'string'],
                'questions.*.category' => ['required'],
                'questions.*.required' => ['boolean'],
                'questions.*.answer_type' => ['required', 'in:text,textarea,select,radio,checkbox'],
                'questions.*.options' => ['array'],
                'questions.*.options.*.label' => ['required_with:questions.*.options', 'string'],
                'questions.*.options.*.value' => ['required_with:questions.*.options', 'string'],
            ], [
                'name.required' => 'Nama Checklist wajib diisi.',
                'sop_code.required' => 'Nomor SOP wajib diisi.',
                'category_id.required' => 'Kategori Checklist wajib dipilih.',
                'type.required' => 'Jenis checklist wajib dipilih.',
                'department_ids.required' => 'Departemen wajib dipilih.',
                'department_ids.array' => 'Departemen harus berupa array.',
                'department_ids.*.required' => 'Departemen wajib dipilih.',
                'department_ids.*.exists' => 'Departemen tidak valid.',
                'questions.required' => 'Minimal harus ada satu pertanyaan.',
                'questions.min' => 'Minimal harus ada satu pertanyaan.',
                'questions.*.title.required' => 'Pertanyaan wajib diisi.',
                'questions.*.answer_type.required' => 'Tipe jawaban wajib dipilih.',
                'questions.*.category.required' => 'Kategori pertanyaan wajib dipilih.',
                'questions.*.options.*.label.required_with' => 'Label opsi wajib diisi.',
                'questions.*.options.*.value.required_with' => 'Value opsi wajib diisi.',
            ]);

            // Check if checklist with same name and sop_code already exists
            $exists = Checklist::where('name', $data['name'])
                ->where('sop_code', $data['sop_code'])
                ->exists();

            if ($exists) {
                return back()->withInput()->with('error', 'Checklist dengan nama dan nomor SOP yang sama sudah ada.');
            }
        } catch (\Illuminate\Validation\ValidationException $e) {
            \Log::error('Validation failed for checklist creation', [
                'errors' => $e->errors(),
                'input' => $request->all()
            ]);
            return back()->withErrors($e->errors())->withInput()->with('error', 'Data tidak valid. Silakan periksa kembali form Anda.');
        }

        try {
            DB::transaction(function () use ($data) {
                // Handle category_id: if it's numeric, use it; otherwise create new category
                $categoryId = null;
                if (!empty($data['category_id'])) {
                    if (is_numeric($data['category_id'])) {
                        // Validate that the category exists
                        if (!ChecklistCategory::where('id', $data['category_id'])->exists()) {
                            throw new \Exception('Kategori Checklist tidak valid.');
                        }
                        $categoryId = (int) $data['category_id'];
                    } else {
                        // Create new category if it doesn't exist
                        $category = ChecklistCategory::firstOrCreate(
                            ['name' => trim($data['category_id'])],
                            ['code' => Str::slug(trim($data['category_id']))]
                        );
                        $categoryId = $category->id;
                    }
                }

                $checklist = Checklist::create([
                    'name' => $data['name'],
                    'sop_code' => $data['sop_code'],
                    'category_id' => $categoryId,
                    'description' => $data['description'] ?? null,
                    'type' => $data['type'] ?? 'single',
                    'status' => 'Active',
                    'durasi' => $data['durasi'] ?? 1,
                    'count' => $data['count'] ?? 1,
                ]);

                // Sync departments to checklist
                if (isset($data['department_ids'])) {
                    $checklist->departments()->sync($data['department_ids']);

                    $employees = [];
                    foreach ($data['department_ids'] as $departmentId) {
                        $department = Department::find($departmentId);
                        if ($department) {
                            $deptEmployees = $department->employees()->pluck('id')->toArray();
                            $employees = array_merge($employees, $deptEmployees);
                        }
                    }
                    // Ensure no duplicate employee ids
                    $employees = array_unique($employees);


                    $checklist->employees()->sync($employees);
                }

                foreach ($data['questions'] as $q) {
                    $categoryId = null;
                    if (!empty($q['category'])) {
                        if (is_numeric($q['category'])) {
                            $categoryId = (int) $q['category'];
                        } else {
                            $category = QuestionCategory::firstOrCreate(
                                ['name' => trim($q['category'])],
                                ['code' => Str::slug(trim($q['category']))]
                            );
                            $categoryId = $category->id;
                        }
                    }

                    $question = $checklist->questions()->create([
                        'question' => $q['title'],
                        'guidance' => $q['description'] ?? null,
                        'required' => $q['required'] ?? false,
                        'answer_type' => $q['answer_type'],
                        'category_id' => $categoryId,
                    ]);

                    if (in_array($q['answer_type'], ['select', 'radio', 'checkbox']) && !empty($q['options'])) {
                        foreach ($q['options'] as $opt) {
                            $question->options()->create([
                                'label' => $opt['label'],
                                'value' => $opt['value'],
                            ]);
                        }
                    }
                }
            });

            \Log::info('Checklist created successfully', ['checklist_name' => $data['name']]);
            return redirect()->route('checklists.index')->with('success', 'Checklist berhasil disimpan!');
        } catch (\Exception $e) {
            \Log::error('Failed to create checklist', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'data' => $data
            ]);
            return back()->withInput()->with('error', 'Terjadi kesalahan saat menyimpan checklist. Silakan coba lagi.');
        }
    }

    public function show(Checklist $checklist)
    {
        abort_unless(Gate::allows('checklists.view'), 403, 'Anda tidak memiliki akses untuk melihat detail checklist');

        $checklist->load([
            'category:id,name',
            'department:id,name,branch_id',
            'department.branch:id,name',
            'questions.category:id,name',
            'questions.options',
            'employees:id,name',
        ]);

        $headerChecklist = [
            'id' => $checklist->id,
            'inspection_number' => $checklist->code_sop ?: ('CHK-' . $checklist->id),
        ];

        $general = [
            'inspection_number' => $checklist->code_sop ?: ('CHK-' . $checklist->id),
            'checklist_name' => $checklist->name,
            'sop_code' => $checklist->code_sop,
            'category' => optional($checklist->category)->name,
            'departments' => $checklist->departments->pluck('name')->implode(', ') ?: '-',
            'branch' => optional(optional($checklist->department)->branch)->name,
            'submit_date' => null,
            'submitted_by' => null,
            'status' => $checklist->status,
            'approved_by' => null,
            'approved_date' => null,
            'remarks' => $checklist->description,
            'location' => null,
            'durasi' => $checklist->durasi ?? 1,
            'count' => $checklist->count ?? 1,
            // Reminder automation settings
            'reminder_enabled' => $checklist->reminder_enabled ?? true,
            'reminder_time' => $checklist->reminder_time ?? '15:20',
            'reminder_frequency' => $checklist->reminder_frequency ?? 'daily',
            'reminder_days' => $checklist->reminder_days ?? [1, 2, 3, 4, 5],
        ];

        $questions = $checklist->questions->map(function ($q) {
            return [
                'question_id' => $q->id,
                'title' => $q->question,
                'category' => optional($q->category)->name,
                'answer' => null,
            ];
        })->values();

        $activities = [
            [
                'at' => $checklist->created_at ? $checklist->created_at->toDateTimeString() : null,
                'action' => 'Checklist dibuat',
                'by' => null,
                'notes' => null,
            ],
            [
                'at' => $checklist->updated_at ? $checklist->updated_at->toDateTimeString() : null,
                'action' => 'Checklist diperbarui',
                'by' => null,
                'notes' => null,
            ],
        ];

        // Get all employees for selection (excluding already attached ones)
        $allEmployees = Employee::with(['department:id,name', 'branch:id,name'])
            ->orderBy('name')
            ->get(['id', 'name', 'department_id', 'branch_id'])
            ->map(function ($emp) {
                return [
                    'id' => $emp->id,
                    'name' => $emp->name,
                    'department' => $emp->department ? ['id' => $emp->department->id, 'name' => $emp->department->name] : null,
                    'branch' => $emp->branch ? ['id' => $emp->branch->id, 'name' => $emp->branch->name] : null,
                ];
            });

        // Get current respondents (employees attached to this checklist)
        $currentRespondents = $checklist->employees->map(function ($emp) {
            return [
                'id' => $emp->id,
                'name' => $emp->name,
                'department' => $emp->department ? ['id' => $emp->department->id, 'name' => $emp->department->name] : null,
                'branch' => $emp->branch ? ['id' => $emp->branch->id, 'name' => $emp->branch->name] : null,
            ];
        });

        // Get branches for filter
        $branches = Branch::orderBy('name')->get(['id', 'name']);

        return Inertia::render('Admin/Checklists/Show', [
            'checklist' => $headerChecklist,
            'general' => $general,
            'questions' => $questions,
            'activities' => $activities,
            'approvals' => [],
            'employees' => $allEmployees,
            'respondents' => $currentRespondents,
            'branches' => $branches,
        ]);
    }

    public function update(Request $request, Checklist $checklist)
    {
        abort_unless(Gate::allows('checklists.edit'), 403, 'Anda tidak memiliki akses untuk mengubah checklist');
        try {
            $data = $request->validate([
                'name' => ['required', 'string', 'max:255'],
                'sop_code' => ['nullable', 'string', 'max:255'],
                'category_id' => ['required'],
                'status' => ['required', 'in:Draft,Active,Inactive'],
                'description' => ['nullable', 'string'],
                'type' => ['required', 'in:single,multiple'],
                'durasi' => ['nullable', 'integer', 'min:1'],
                'count' => ['nullable', 'integer', 'min:1'],
                // Reminder automation fields
                'reminder_enabled' => ['boolean'],
                'reminder_time' => ['nullable', 'date_format:H:i'],
                'reminder_frequency' => ['nullable', 'in:daily,weekly,monthly'],
                'reminder_days' => ['nullable', 'array'],
                'reminder_days.*' => ['integer', 'min:0', 'max:6'],
                'department_ids' => ['required', 'array'],
                'department_ids.*' => ['required', 'exists:departments,id'],
                'questions' => ['required', 'array', 'min:1'],
                'questions.*.title' => ['required', 'string'],
                'questions.*.description' => ['nullable', 'string'],
                'questions.*.answer_type' => ['required', 'in:text,textarea,select,radio,checkbox'],
                'questions.*.required' => ['boolean'],
                'questions.*.category' => ['required'],
                'questions.*.options' => ['array'],
                'questions.*.options.*.label' => ['required_with:questions.*.options', 'string'],
                'questions.*.options.*.value' => ['required_with:questions.*.options', 'string'],
            ], [
                'department_ids.required' => 'Departemen wajib dipilih.',
                'department_ids.array' => 'Departemen harus berupa array.',
                'department_ids.*.required' => 'Departemen wajib dipilih.',
                'department_ids.*.exists' => 'Departemen tidak valid.',
                'category_id.required' => 'Kategori Checklist wajib dipilih.',
                'questions.*.title.required' => 'Pertanyaan wajib diisi.',
                'questions.*.answer_type.required' => 'Tipe jawaban wajib dipilih.',
                'questions.*.category.required' => 'Kategori pertanyaan wajib dipilih.',
                'questions.*.options.*.label.required_with' => 'Label opsi wajib diisi.',
                'questions.*.options.*.value.required_with' => 'Value opsi wajib diisi.',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error('Validation failed for checklist update', [
                'errors' => $e->errors(),
                'input' => $request->all()
            ]);
            return back()->withErrors($e->errors())->withInput()->with('error', 'Data tidak valid. Silakan periksa kembali form Anda.');
        }

        try {
            DB::transaction(function () use ($checklist, $data, $request) {
                // Handle category_id: if it's numeric, use it; otherwise create new category
                $categoryId = null;
                if (!empty($data['category_id'])) {
                    if (is_numeric($data['category_id'])) {
                        // Validate that the category exists
                        if (!ChecklistCategory::where('id', $data['category_id'])->exists()) {
                            throw new \Exception('Kategori Checklist tidak valid.');
                        }
                        $categoryId = (int) $data['category_id'];
                    } else {
                        // Create new category if it doesn't exist
                        $category = ChecklistCategory::firstOrCreate(
                            ['name' => trim($data['category_id'])],
                            ['name' => trim($data['category_id'])]
                        );
                        $categoryId = $category->id;
                    }
                }

                $checklist->update([
                    'name' => $data['name'],
                    'sop_code' => $data['sop_code'] ?? null,
                    'category_id' => $categoryId,
                    'description' => $data['description'] ?? null,
                    'type' => $data['type'] ?? 'single',
                    'status' => $data['status'] ?? $checklist->status,
                    'durasi' => $data['durasi'] ?? null,
                    'count' => $data['count'] ?? null,
                    // Reminder automation
                    'reminder_enabled' => $request->has('reminder_enabled') ? (bool) $data['reminder_enabled'] : false,
                    'reminder_time' => $data['reminder_time'] ?? null,
                    'reminder_frequency' => $data['reminder_frequency'] ?? 'daily',
                    'reminder_days' => isset($data['reminder_days']) ? json_encode($data['reminder_days']) : null,
                ]);

                // Sync departments to checklist
                if (isset($data['department_ids'])) {
                    $checklist->departments()->sync($data['department_ids']);

                    $employees = [];
                    foreach ($data['department_ids'] as $departmentId) {
                        $department = Department::find($departmentId);
                        if ($department) {
                            $deptEmployees = $department->employees()->pluck('id')->toArray();
                            $employees = array_merge($employees, $deptEmployees);
                        }
                    }
                    // Ensure no duplicate employee ids
                    $employees = array_unique($employees);


                    $checklist->employees()->sync($employees);
                }

                if (isset($data['questions'])) {
                    foreach ($checklist->questions as $old) {
                        $old->options()->delete();
                    }
                    $checklist->questions()->delete();

                    foreach ($data['questions'] as $q) {
                        $categoryId = null;
                        if (!empty($q['category'])) {
                            if (is_numeric($q['category'])) {
                                $categoryId = (int) $q['category'];
                            } else {
                                $qc = QuestionCategory::firstOrCreate(
                                    ['name' => trim($q['category'])],
                                    ['code' => Str::slug(trim($q['category']))]
                                );
                                $categoryId = $qc->id;
                            }
                        }

                        $question = $checklist->questions()->create([
                            'question' => $q['title'],
                            'guidance' => $q['description'] ?? null,
                            'required' => $q['required'] ?? false,
                            'answer_type' => $q['answer_type'],
                            'category_id' => $categoryId,
                        ]);

                        if (in_array($q['answer_type'], ['select', 'radio', 'checkbox']) && !empty($q['options'])) {
                            foreach ($q['options'] as $opt) {
                                $question->options()->create([
                                    'label' => $opt['label'],
                                    'value' => $opt['value'],
                                ]);
                            }
                        }
                    }
                }
            });

            Log::info('Checklist updated successfully', ['checklist_id' => $checklist->id, 'checklist_name' => $data['name']]);
            return redirect()->route('checklists.index')->with('success', 'Checklist berhasil diubah');
        } catch (\Throwable $e) {
            Log::error('Checklist update error', ['err' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            return redirect()->back()->with('error', 'Gagal mengubah checklist');
        }
    }

    /**
     * Trigger checklist reminder manually (for development/testing only)
     * This method bypasses schedule checks and directly sends notifications
     * for a specific checklist to its respondents
     */
    public function sendReminder(Request $request)
    {
        // Only allow in local/dev environment
        if (!in_array(config('app.env'), ['local', 'development', 'dev'])) {
            return response()->json([
                'success' => false,
                'message' => 'This feature is only available in development environment'
            ], 403);
        }

        $request->validate([
            'checklist_id' => 'required|exists:checklists,id'
        ]);

        try {
            $checklistId = $request->checklist_id;
            $checklist = Checklist::findOrFail($checklistId);

            if (!$checklist->reminder_enabled) {
                return response()->json([
                    'success' => false,
                    'message' => 'Reminder tidak aktif untuk checklist ini'
                ], 400);
            }

            $notificationService = app(\App\Services\NotificationService::class);
            $today = \Carbon\Carbon::today('Asia/Makassar');
            $totalSent = 0;

            // Get respondents (employees) assigned to this checklist
            $respondents = \App\Models\CheklistEmployee::where('checklist_id', $checklistId)
                ->with(['employee.user'])
                ->get();

            Log::info("[ChecklistReminder] Respondents loaded", [
                'checklist_id' => $checklistId,
                'checklist_name' => $checklist->name,
                'total_respondents' => $respondents->count(),
                'respondent_ids' => $respondents->pluck('employee_id')->toArray(),
            ]);

            foreach ($respondents as $assignment) {
                $employee = $assignment->employee;

                if (!$employee || !$employee->user || !$employee->user->external_id) {
                    Log::warning("[ChecklistReminder] Skipped: employee or user not found", [
                        'checklist_id' => $checklistId,
                        'assignment_id' => $assignment->id,
                        'employee_id' => $assignment->employee_id,
                        'has_employee' => !!$employee,
                        'has_user' => $employee ? !!$employee->user : false,
                    ]);
                    continue;
                }

                // Check if this employee already completed this checklist today
                $completedToday = \App\Models\Inspection::where('checklist_id', $checklistId)
                    ->whereDate('submit_date', $today)
                    ->where(function ($q) use ($employee) {
                        $q->where('submitted_by', $employee->user_id)
                            ->orWhere('created_by', $employee->user_id);
                    })
                    ->exists();

                // Skip if already completed
                if ($completedToday) {
                    Log::info("[ChecklistReminder] Skipped: already completed today", [
                        'checklist_id' => $checklistId,
                        'employee_id' => $employee->id,
                        'employee_name' => $employee->name,
                        'date' => $today->format('Y-m-d'),
                    ]);
                    continue;
                }

                // Send reminder to this employee
                $message = "Kamu memiliki tugas mengisi checklist: {$checklist->name}";

                Log::info("[ChecklistReminder] Sending reminder", [
                    'checklist_id' => $checklistId,
                    'employee_id' => $employee->id,
                    'employee_name' => $employee->name,
                    'external_id' => $employee->user->external_id ?? null,
                ]);

                try {
                    $notificationService->notifyStaffOnChecklistReminder(
                        $employee->user->external_id,
                        'Reminder Checklist',
                        $message,
                        [
                            'type' => 'checklist_reminder',
                            'pending_count' => 1,
                            'checklist_ids' => [$checklistId],
                            'checklists' => [$checklist->name],
                            'date' => $today->format('Y-m-d'),
                        ]
                    );
                    $totalSent++;
                    Log::info("[ChecklistReminder] Reminder sent successfully", [
                        'checklist_id' => $checklistId,
                        'employee_id' => $employee->id,
                        'employee_name' => $employee->name,
                    ]);
                } catch (\Exception $e) {
                    Log::warning("[ChecklistReminder] Failed to send reminder", [
                        'checklist_id' => $checklistId,
                        'employee_id' => $employee->id,
                        'employee_name' => $employee->name,
                        'error' => $e->getMessage(),
                    ]);
                }
            }

            \Log::info('Manual checklist reminder triggered', [
                'user_id' => auth()->id(),
                'checklist_id' => $checklistId,
                'checklist_name' => $checklist->name,
                'total_sent' => $totalSent,
            ]);

            if ($totalSent === 0) {
                return response()->json([
                    'success' => true,
                    'message' => 'Tidak ada reminder yang dikirim. Semua responden sudah mengisi checklist hari ini atau tidak memiliki external_id.',
                    'total_sent' => 0,
                ]);
            }

            return response()->json([
                'success' => true,
                'message' => "Reminder '{$checklist->name}' berhasil dikirim ke {$totalSent} karyawan!",
                'total_sent' => $totalSent,
            ]);
        } catch (\Exception $e) {
            \Log::error('Manual checklist reminder failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Gagal mengirim reminder: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function destroy(Checklist $checklist)
    {
        abort_unless(Gate::allows('checklists.delete'), 403, 'Anda tidak memiliki akses untuk menghapus checklist');

        try {
            // Check if checklist is used in inspections
            $inspectionCount = DB::table('inspections')
                ->where('checklist_id', $checklist->id)
                ->count();

            if ($inspectionCount > 0) {
                return redirect()->back()->with(
                    'error',
                    "Checklist tidak dapat dihapus karena sudah terhubung dengan {$inspectionCount} data inspeksi."
                );
            }

            // Check if checklist has related data (questions, answers, etc)
            $hasQuestions = $checklist->questions()->exists();

            DB::transaction(function () use ($checklist, $hasQuestions) {
                // Detach departments relationship (delete from pivot table)
                $checklist->departments()->detach();

                // Detach employees relationship (delete from pivot table)
                $checklist->employees()->detach();

                if ($hasQuestions) {
                    // Delete related data first
                    foreach ($checklist->questions as $question) {
                        $question->options()->delete();
                        $question->answers()->delete();
                    }
                    $checklist->questions()->delete();
                }

                $checklist->delete();
            });

            Log::info('Checklist deleted successfully', ['checklist_id' => $checklist->id]);
            return redirect()->back()->with('success', 'Checklist berhasil dihapus');
        } catch (\Throwable $e) {
            Log::error('Checklist destroy error', ['err' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            return redirect()->back()->with('error', 'Gagal menghapus checklist: ' . $e->getMessage());
        }
    }
}
