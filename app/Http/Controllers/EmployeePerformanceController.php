<?php

namespace App\Http\Controllers;

use App\Http\Resources\EmployeePerformanceResource;
use App\Models\Employee;
use App\Models\EmployeePerformance;
use App\Models\Branch;
use App\Models\Department;
use App\Actions\Data\EmployeePerformance\CalculateKPI;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class EmployeePerformanceController extends Controller
{
    public function index(Request $request)
    {
        $q = $request->string('q')->toString();
        $branchId = $request->integer('branch_id') ?: null;
        $departmentId = $request->integer('department_id') ?: null;
        $status = $request->input('status') ?: null;
        $perPage = $request->integer('per_page') ?: 15;
        $month = $request->integer('month') ?: now()->month;
        $year = $request->integer('year') ?: now()->year;

        $user = Auth::user();
        /** @var \App\Models\User $user */
        $isSuperadmin = $user->hasRole('Superadmin');
        $userBranchId = $user->employee->branch_id ?? null;

        // If user is not Superadmin and not Branch 2, force filter by their branch_id
        if (!$isSuperadmin && $userBranchId && $userBranchId != 2) {
            $branchId = $userBranchId;
        }

        // Get branches and departments for filters
        $branches = Branch::select('id', 'name')
            ->when(!$isSuperadmin && $userBranchId && $userBranchId != 2, function ($query) use ($userBranchId) {
                $query->where('id', $userBranchId);
            })
            ->get();

        $departments = Department::select('id', 'name', 'branch_id')
            ->when($branchId, function ($query) use ($branchId) {
                $query->where('branch_id', $branchId);
            })
            ->when(!$isSuperadmin && $userBranchId && $userBranchId != 2, function ($query) use ($userBranchId) {
                $query->where('branch_id', $userBranchId);
            })
            ->get();

        if ($request->input('tab') === 'history' || $request->input('tab') === 'riwayat') {
            $query = EmployeePerformance::with(['employee.branch', 'employee.department', 'employee.position', 'reporter'])
                ->when($q, function ($query) use ($q) {
                    $query->whereHas('employee', function ($qry) use ($q) {
                        $qry->where('name', 'like', "%{$q}%")
                            ->orWhereHas('branch', function ($b) use ($q) {
                                $b->where('name', 'like', "%{$q}%");
                            })
                            ->orWhereHas('department', function ($d) use ($q) {
                                $d->where('name', 'like', "%{$q}%");
                            })
                            ->orWhereHas('position', function ($p) use ($q) {
                                $p->where('name', 'like', "%{$q}%");
                            });
                    });
                })
                ->whereIn('category', ['Kerjasama', 'Keterampilan', 'Disiplin'])
                ->when($branchId, function ($query) use ($branchId) {
                    $query->whereHas('employee', function ($q) use ($branchId) {
                        $q->where('branch_id', $branchId);
                    });
                })
                ->when($departmentId, function ($query) use ($departmentId) {
                    $query->whereHas('employee', function ($q) use ($departmentId) {
                        $q->where('department_id', $departmentId);
                    });
                })
                ->when($status, function ($query) use ($status) {
                    $query->whereHas('employee.user', function ($q) use ($status) {
                        $q->where('status', $status);
                    });
                })
                ->where('month', $month)
                ->where('year', $year);

            $employeePerformances = $query->orderBy('created_at', 'desc')
                ->paginate($perPage)
                ->withQueryString()
                ->through(function ($performance) use ($user, $isSuperadmin) {
                    $categoryMap = [
                        'Keterampilan' => 'Productivity',
                        'Kerjasama' => 'Attitude',
                        'Disiplin' => 'Initiative',
                        'Kuantitas' => 'Checklist',
                    ];
                    $displayCategory = $categoryMap[$performance->category] ?? $performance->category;

                    // Get reporter name
                    $reporterName = $performance->reporter?->name ?? "Superadmin";

                    // Censor reporter name if user is not superadmin AND not the reporter
                    if (!$isSuperadmin && $performance->reporter_id !== $user->id) {
                        $reporterName = "******";
                    }

                    return [
                        'id' => $performance->id,
                        'employee_id' => $performance->employee_id,
                        'employee' => $performance->employee?->name,
                        'gender' => $performance->employee?->gender,
                        'cabang' => $performance->employee?->branch?->name,
                        'category_raw' => $performance->category,
                        'category' => $displayCategory,
                        'penilai' => $reporterName,
                        'score' => $this->calculateDisplayScore($performance->score, $performance->category),
                        'created_at' => $performance->created_at,
                    ];
                });
        } else {
            // Build query with performances filtered by month and year
            $query = Employee::with([
                'performances' => function ($q) use ($month, $year) {
                    $q->where('month', $month)->where('year', $year);
                },
                'branch',
                'department',
                'position'
            ]);

            // Search filter
            if ($q) {
                $query->where(function ($qry) use ($q) {
                    $qry->where('name', 'like', "%{$q}%")
                        ->orWhereHas('branch', function ($b) use ($q) {
                            $b->where('name', 'like', "%{$q}%");
                        })
                        ->orWhereHas('department', function ($d) use ($q) {
                            $d->where('name', 'like', "%{$q}%");
                        })
                        ->orWhereHas('position', function ($p) use ($q) {
                            $p->where('name', 'like', "%{$q}%");
                        });
                });
            }

            // Branch filter
            if ($branchId) {
                $query->where('branch_id', $branchId);
            } elseif (!$isSuperadmin && $userBranchId && $userBranchId != 2) {
                $query->where('branch_id', $userBranchId);
            }

            // Department filter
            if ($departmentId) {
                $query->where('department_id', $departmentId);
            }

            // Status filter (from user status)
            if ($status) {
                $query->whereHas('user', function ($q) use ($status) {
                    $q->where('status', $status);
                });
            }

            $isSuperadmin = $user->hasRole('superadmin');

            // Get all employees matching the filters (without pagination first)
            $allEmployees = $query->get();

            // Transform data and calculate total score for each employee
            $employeesWithScores = $allEmployees->map(function ($employee) use ($isSuperadmin) {
                $kehadiran = $this->calculateDisplayScore($employee->performances->where('category', 'Kehadiran')->first()?->score, 'Kehadiran');
                $checklist = $this->calculateDisplayScore($employee->performances->where('category', 'Kuantitas')->first()?->score, 'Kuantitas');
                $productivity = $this->calculateAverageScore($employee->performances, 'Keterampilan');
                $attitude = $this->calculateAverageScore($employee->performances, 'Kerjasama');
                $initiative = $this->calculateAverageScore($employee->performances, 'Disiplin');

                // Calculate total score
                $totalScore = $kehadiran + $checklist + $productivity + $attitude + $initiative;

                return [
                    'id' => $employee->id,
                    'employee' => $employee->name,
                    'cabang' => $employee->branch?->name,
                    'departemen' => $employee->department?->name,
                    'jabatan' => $employee->position?->name,
                    'Kehadiran' => $kehadiran,
                    'Checklist' => $checklist,
                    'Productivity' => $productivity,
                    'Attitude' => $attitude,
                    'Initiative' => $initiative,
                    'total_score' => round($totalScore, 1),
                ];
            });

            // Sort by total score (highest to lowest)
            $sortedEmployees = $employeesWithScores->sortByDesc('total_score')->values();

            // Manual pagination
            $currentPage = $request->get('page', 1);
            $offset = ($currentPage - 1) * $perPage;
            $paginatedItems = $sortedEmployees->slice($offset, $perPage)->values();

            // Create pagination structure
            $employeePerformances = new \Illuminate\Pagination\LengthAwarePaginator(
                $paginatedItems,
                $sortedEmployees->count(),
                $perPage,
                $currentPage,
                [
                    'path' => $request->url(),
                    'query' => $request->query(),
                ]
            );
            $employeePerformances->withQueryString();
        }

        return Inertia::render('EmployeePerformances/Index', [
            'employeePerformances' => $employeePerformances,
            'filters' => $request->only(['q', 'branch_id', 'department_id', 'status', 'per_page', 'month', 'year', 'tab']),
            'branches' => $branches,
            'departments' => $departments,
        ]);
    }

    /**
     * Calculate average score for a category from a collection of performances
     */
    private function calculateAverageScore($performances, $category)
    {
        $filtered = $performances->where('category', $category);

        if ($filtered->isEmpty()) {
            return 0;
        }

        $totalScore = 0;
        $count = 0;

        foreach ($filtered as $performance) {
            $totalScore += $this->calculateDisplayScore($performance->score, $category);
            $count++;
        }

        return $count > 0 ? round($totalScore / $count, 1) : 0;
    }

    public function getScore(Request $request, $employeeId)
    {
        $category = $request->input('category');
        $month = $request->integer('month') ?: now()->month;
        $year = $request->integer('year') ?: now()->year;

        // Map category from display name to database value
        $categoryMap = [
            'Productivity' => 'Keterampilan',
            'Attitude' => 'Kerjasama',
            'Initiative' => 'Disiplin',
        ];

        $dbCategory = $categoryMap[$category] ?? $category;

        $performance = EmployeePerformance::where('employee_id', $employeeId)
            ->where('category', $dbCategory)
            ->where('month', $month)
            ->where('year', $year)
            ->first();

        $employee = Employee::findOrFail($employeeId);

        // Get category info for indicators
        $categoryInfo = $this->getCategoryInfo($category);
        $indicators = $categoryInfo['items'] ?? [];

        // Initialize score structure if not exists
        $scoreData = $performance?->score ?? [];
        if (empty($scoreData) || !is_array($scoreData)) {
            $scoreData = [];
            foreach ($indicators as $indicator) {
                $scoreData[$indicator] = null;
            }
        }

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $performance?->id,
                'employee_id' => $employeeId,
                'employee_name' => $employee->name,
                'category' => $category,
                'score' => $scoreData,
                'notes' => $performance?->notes,
                'month' => $month,
                'year' => $year,
            ],
        ]);
    }

    public function getScoreDetails(Request $request, $employeeId)
    {
        $category = $request->input('category');
        $month = $request->integer('month') ?: now()->month;
        $year = $request->integer('year') ?: now()->year;
        $q = $request->string('q')->toString();
        $perPage = $request->integer('per_page') ?: 10;

        // Check if user is superadmin
        $user = Auth::user();
        /** @var \App\Models\User $user */
        $isSuperadmin = $user->hasRole('Superadmin');

        // Map category from display name to database value
        $categoryMap = [
            'Productivity' => 'Keterampilan',
            'Attitude' => 'Kerjasama',
            'Initiative' => 'Disiplin',
        ];

        $dbCategory = $categoryMap[$category] ?? $category;

        // Base Query
        $query = EmployeePerformance::with('reporter')
            ->where('employee_id', $employeeId)
            ->where('category', $dbCategory)
            ->where('month', $month)
            ->where('year', $year);

        // Calculate average score for ALL records in this category (unfiltered)
        $allPerformancesValues = (clone $query)->get();
        $totalAccumulated = 0;
        $count = 0;
        foreach ($allPerformancesValues as $perf) {
            $totalAccumulated += $this->calculateDisplayScore($perf->score, $dbCategory);
            $count++;
        }
        $averageScore = $count > 0 ? $totalAccumulated / $count : 0;


        // Apply Search Filter if exists
        if ($q) {
            $query->whereHas('reporter', function ($qry) use ($q) {
                $qry->where('name', 'like', "%{$q}%");
            });
        }

        // Apply Pagination
        $performances = $query->paginate($perPage)->withQueryString();

        $employee = Employee::findOrFail($employeeId);
        $categoryInfo = $this->getCategoryInfo($category);
        $indicators = $categoryInfo['items'] ?? [];

        // Transform paginated data
        $transformed = $performances->through(function ($perf) use ($dbCategory, $indicators, $isSuperadmin) {
            $scoreData = $perf->score;
            $displayScore = $this->calculateDisplayScore($scoreData, $dbCategory);

            // Censor reporter name for non-superadmin
            $reporterName = $perf->reporter ? $perf->reporter->name : 'Superadmin';
            if (!$isSuperadmin) {
                $reporterName = "******";
            }

            // Generate row data
            $row = [
                'id' => $perf->id,
                'reporter_name' => $reporterName,
                'scores' => [],
                'total' => $displayScore
            ];

            // Fill indicator scores
            foreach ($indicators as $indicator) {
                // Check if scoreData is array and has key
                if (is_array($scoreData) && isset($scoreData[$indicator])) {
                    $row['scores'][$indicator] = $scoreData[$indicator];
                } else {
                    $row['scores'][$indicator] = 0;
                }
            }

            return $row;
        });

        return response()->json([
            'success' => true,
            'data' => [
                'employee_name' => $employee->name,
                'category' => $category,
                'average_score' => round($averageScore, 1),
                'indicators' => $indicators,
                'performances' => $transformed
            ]
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'category' => 'required|string',
            'score' => 'required|array',
            'score.*' => 'nullable|numeric|min:0|max:10',
            'notes' => 'nullable|string',
            'month' => 'required|integer|min:1|max:12',
            'year' => 'required|integer|min:2000|max:2100',
        ]);

        // Ubah nilai null pada score.* menjadi 0
        $score = $validated['score'];
        foreach ($score as $key => $value) {
            if (is_null($value)) {
                $score[$key] = 0;
            }
        }
        $request->merge(['score' => $score]);


        // Map category from display name to database value
        $categoryMap = [
            'Productivity' => 'Keterampilan',
            'Attitude' => 'Kerjasama',
            'Initiative' => 'Disiplin',
        ];

        $dbCategory = $categoryMap[$request->category] ?? $request->category;

        // Check if already exists
        $existing = EmployeePerformance::where('employee_id', $request->employee_id)
            ->where('category', $dbCategory)
            ->where('month', $request->month)
            ->where('year', $request->year)
            ->first();

        if ($existing) {
            return response()->json([
                'success' => false,
                'message' => 'Score untuk kategori ini sudah ada. Gunakan update untuk mengubah.',
            ], 422);
        }

        $performance = EmployeePerformance::create([
            'employee_id' => $request->employee_id,
            'category' => $dbCategory,
            'score' => $request->score,
            'notes' => $request->notes,
            'month' => $request->month,
            'year' => $request->year,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Score berhasil disimpan',
            'data' => $performance,
        ]);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'score' => 'required|array',
            'score.*' => 'nullable|numeric|min:0|max:10',
            'notes' => 'nullable|string',
        ]);

        $performance = EmployeePerformance::findOrFail($id);

        $performance->update([
            'score' => $request->score,
            'notes' => $request->notes,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Score berhasil diupdate',
            'data' => $performance,
        ]);
    }

    public function calculateKPI(Request $request)
    {
        $month = now()->month;
        $year =  now()->year;
        $branchId = $request->integer('branch_id') ?: null;

        $user = Auth::user();
        /** @var \App\Models\User $user */
        $isSuperadmin = $user->hasRole('Superadmin');
        $userBranchId = $user->employee->branch_id ?? null;

        try {
            $result = app(CalculateKPI::class)->execute(
                $month,
                $year,
                $branchId,
                $userBranchId,
                $isSuperadmin
            );

            return response()->json($result);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghitung KPI: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Calculate display score from JSON score data
     * Formula: ((Total nilai input sub indikator / jumlah sub indikator) * 10) * bobot indikator
     * For Kehadiran and Checklist: score * bobot
     */
    private function calculateDisplayScore($scoreData, $category)
    {
        // Handle format { score: value } for Kehadiran and Checklist
        if (is_array($scoreData) && isset($scoreData['score']) && is_numeric($scoreData['score'])) {
            $baseScore = $scoreData['score'];

            // Apply weight for Kehadiran and Checklist
            $weights = [
                'Kehadiran' => 0.25,  // 25%
                'Kuantitas' => 0.20,   // 20% (Checklist)
            ];

            $weight = $weights[$category] ?? 1.0;
            return round($baseScore * $weight, 1);
        }

        // Legacy support: For Kehadiran and Kuantitas (Checklist), score might be numeric or stored as array with single numeric value
        if (is_numeric($scoreData)) {
            $weights = [
                'Kehadiran' => 0.25,  // 25%
                'Kuantitas' => 0.20,   // 20% (Checklist)
            ];
            $weight = $weights[$category] ?? 1.0;
            return round($scoreData * $weight, 1);
        }

        // If it's an array with numeric keys (stored numeric value), extract it
        if (is_array($scoreData) && count($scoreData) === 1 && isset($scoreData[0]) && is_numeric($scoreData[0])) {
            $weights = [
                'Kehadiran' => 0.25,  // 25%
                'Kuantitas' => 0.20,   // 20% (Checklist)
            ];
            $weight = $weights[$category] ?? 1.0;
            return round($scoreData[0] * $weight, 1);
        }

        // For other categories, score is JSON object with indicator names as keys
        if (!is_array($scoreData) || empty($scoreData)) {
            return 0;
        }

        // Check if it's an associative array (indicator-based scoring)
        $isAssociative = array_keys($scoreData) !== range(0, count($scoreData) - 1);

        if (!$isAssociative) {
            // If it's a numeric array, treat as single value
            if (count($scoreData) === 1 && is_numeric($scoreData[0])) {
                return round($scoreData[0], 1);
            }
            return 0;
        }

        // Get category weight for Productivity, Attitude, Initiative
        $weights = [
            'Keterampilan' => 0.25,  // Productivity: 25%
            'Kerjasama' => 0.15,     // Attitude: 15%
            'Disiplin' => 0.15,      // Initiative: 15%
        ];

        $weight = $weights[$category] ?? 1.0;

        // Filter out null values and calculate average
        $validScores = array_filter($scoreData, fn($v) => $v !== null && $v !== '');
        $count = count($validScores);

        if ($count === 0) {
            return 0;
        }

        $total = array_sum($validScores);

        // Formula: ((Total nilai / jumlah) * 10) * bobot
        $result = (($total / $count) * 20) * $weight;

        return round($result, 1);
    }

    /**
     * Get category information with indicators
     */
    private function getCategoryInfo($category)
    {
        $categoryInfo = [
            'Productivity' => [
                'title' => 'KPI Kinerja & Output Kerja (Work Quality & Productivity)',
                'items' => [
                    'Kualitas pekerjaan (rapi, sesuai SOP)',
                    'Kecepatan dan hasil kerja',
                    'Zero mistake / zero repeat job',
                    'Inisiatif menyelesaikan masalah',
                    'Produktivitas per jam/per shift',
                ],
            ],
            'Attitude' => [
                'title' => 'KPI Perilaku & Sikap Kerja (Behavior & Attitude)',
                'items' => [
                    'Kerjasama tim',
                    'Komunikasi',
                    'Etika kerja',
                    'Tidak menyalahkan orang lain',
                    'Tidak menciptakan konflik',
                    'Sopan santun terhadap atasan & rekan',
                ],
            ],
            'Initiative' => [
                'title' => 'KPI Inisiatif & Pengembangan (Initiative & Improvement)',
                'items' => [
                    'Ide perbaikan',
                    'Meningkatkan efisiensi',
                    'Membantu tugas tambahan',
                    'Kemauan belajar & upgrade skill',
                    'Melatih junior/anggota tim',
                ],
            ],
        ];

        return $categoryInfo[$category] ?? null;
    }
}
