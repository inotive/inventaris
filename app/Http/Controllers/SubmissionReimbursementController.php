<?php

namespace App\Http\Controllers;

use App\Models\Reimbursement;
use App\Models\Submission;
use App\Enums\SubmissionTypeEnum;
use App\Http\Resources\SubmissionAllResource;
use App\Models\Branch;
use App\Actions\Data\Submission\Referrer\GetBrancByRole;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class SubmissionReimbursementController extends Controller
{
    public function index(Request $request)
    {
        $sortBy = $request->get('sort_by', 'created_at');
        $sortDirection = $request->get('sort_direction', 'desc');
        $search = $request->input('search');
        $perPage = (int) $request->input('per_page', 10);

        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');
        $nameFilter = $request->input('name_filter');
        $branchFilter = $request->input('branch_filter');
        $statusFilter = $request->input('status_filter');
        $titleFilter = $request->input('title_filter');
        $eventStartDate = $request->input('event_start_date');
        $eventEndDate = $request->input('event_end_date');

        // Filter by branch for non-superadmin users
        if (!Auth::user()->hasRole('Superadmin') && Auth::user()->employee->branch_id != 2) {
            $branchFilter = Auth::user()->employee->branch_id;
        }

        $query = Reimbursement::with(['employee.branch', 'approvedBy'])
            ->when($startDate, fn($q) => $q->whereDate('created_at', '>=', $startDate))
            ->when($endDate, fn($q) => $q->whereDate('created_at', '<=', $endDate))
            ->when($nameFilter, function ($q) use ($nameFilter) {
                $q->whereHas('employee', function ($w) use ($nameFilter) {
                    $w->where('name', 'like', "%{$nameFilter}%");
                });
            })
            ->when($branchFilter, function ($q) use ($branchFilter) {
                $q->whereHas('employee.branch', function ($w) use ($branchFilter) {
                    $w->where('id', $branchFilter);
                });
            })
            ->when($statusFilter, fn($q) => $q->where('status', $statusFilter))
            ->when($titleFilter, fn($q) => $q->where('title', 'like', "%{$titleFilter}%"))
            ->when($eventStartDate, fn($q) => $q->whereDate('event_date', '>=', $eventStartDate))
            ->when($eventEndDate, fn($q) => $q->whereDate('event_date', '<=', $eventEndDate));

        $data = $query->orderBy($sortBy, $sortDirection)->paginate($perPage)->withQueryString();

        // Set submission_type on each item for the resource
        $data->getCollection()->each(function ($item) {
            $item->submission_type = 'reimbursement';
        });

        $submissions = SubmissionAllResource::collection($data)->response()->getData(true);

        // Calculate statistics - filter by branch for non-superadmin
        $statisticsQuery = Reimbursement::query();
        if (!Auth::user()->hasRole('Superadmin') && Auth::user()->employee->branch_id != 2 && $branchFilter) {
            $statisticsQuery->whereHas('employee.branch', function ($w) use ($branchFilter) {
                $w->where('id', $branchFilter);
            });
        }

        $statistics = [
            'pending' => (clone $statisticsQuery)->where('status', 'pending')->count(),
            'approved' => (clone $statisticsQuery)->where('status', 'approved')->count(),
            'rejected' => (clone $statisticsQuery)->where('status', 'rejected')->count(),
            'cancelled' => (clone $statisticsQuery)->where('status', 'cancelled')->count(),
        ];

        $submissionTypes = collect(SubmissionTypeEnum::cases())->map(function ($type) {
            return [
                'value' => $type->value,
                'label' => $type->label()['label'],
                'name' => $type->label()['name'],
                'permission' => $type->label()['permission'],
            ];
        });
        $submissionStatuses = [
            ['value' => 'pending', 'label' => 'Menunggu'],
            ['value' => 'approved', 'label' => 'Disetujui'],
            ['value' => 'rejected', 'label' => 'Ditolak'],
            ['value' => 'cancelled', 'label' => 'Dibatalkan'],
        ];

        // Get branches for filter - based on user role
        $branches = app(GetBrancByRole::class)->execute(Auth::user());

        return Inertia::render('Admin/Submission/Index', [
            'submissions' => $submissions,
            'sort_by' => $sortBy,
            'sort_direction' => $sortDirection,
            'search' => $search,
            'start_date' => $startDate,
            'end_date' => $endDate,
            'name_filter' => $nameFilter,
            'branch_filter' => $branchFilter,
            'type_filter' => SubmissionTypeEnum::REIMBURSEMENT->value,
            'status_filter' => $statusFilter,
            'title_filter' => $titleFilter,
            'event_start_date' => $eventStartDate,
            'event_end_date' => $eventEndDate,
            'branches' => $branches,
            'submission_types' => $submissionTypes,
            'submission_statuses' => $submissionStatuses,
            'statistics' => $statistics,
            'type' => 'reimbursement',
            'types' => Submission::TYPES,
        ]);
    }

    public function show(Request $request, $id)
    {
        try {
            $reimbursement = Reimbursement::with(['employee.branch'])->findOrFail($id);

            $submissionTypes = collect(SubmissionTypeEnum::cases())->map(function ($type) {
                return [
                    'value' => $type->value,
                    'label' => $type->label(),
                ];
            });

            $submissionStatuses = [
                ['value' => 'pending', 'label' => 'Menunggu'],
                ['value' => 'approved', 'label' => 'Disetujui'],
                ['value' => 'rejected', 'label' => 'Ditolak'],
                ['value' => 'cancelled', 'label' => 'Dibatalkan'],
            ];

            $data = [
                'id' => $reimbursement->id,
                'employee' => [
                    'id' => optional($reimbursement->employee)->id,
                    'name' => optional($reimbursement->employee)->name,
                    'branch_name' => optional($reimbursement->employee?->branch)->name,
                ],
                'title' => $reimbursement->title,
                'event_date' => $reimbursement->event_date,
                'amount' => $reimbursement->amount,
                'currency' => $reimbursement->currency ?? 'IDR',
                'description' => $reimbursement->description,
                'attachment_path' => $reimbursement->attachment_path,
                'status' => $reimbursement->status ?? 'pending',
                'admin_notes' => $reimbursement->admin_notes,
                'approved_by' => $reimbursement->approvedBy?->name,
                'approved_at' => optional($reimbursement->approved_at)?->format('Y-m-d H:i:s'),
                'created_at' => optional($reimbursement->created_at)?->format('Y-m-d H:i:s'),
                'updated_at' => optional($reimbursement->updated_at)?->format('Y-m-d H:i:s'),
            ];

            // Check if request expects JSON (API call)
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json([
                    'success' => true,
                    'data' => $data,
                    'submission_types' => $submissionTypes,
                    'submission_statuses' => $submissionStatuses,
                    'type' => 'reimbursement'
                ]);
            }

            // Return Inertia response for regular page requests
            return Inertia::render('Admin/Submission/components/DetailReimbursement', [
                'submission' => $data,
                'type' => 'reimbursement',
                'submission_types' => $submissionTypes,
                'submission_statuses' => $submissionStatuses
            ]);
        } catch (\Exception $e) {
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Terjadi kesalahan saat mengambil data reimbursement',
                    'error' => $e->getMessage()
                ], 500);
            }

            throw $e;
        }
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'status' => 'required',
            'admin_notes' => 'nullable|string|max:1000',
        ]);

        $reimbursement = Reimbursement::find($id);

        if (!$reimbursement) {
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Reimbursement tidak ditemukan'
                ], 404);
            }
            abort(404, 'Reimbursement tidak ditemukan');
        }

        $reimbursement->status = $request->status;
        $reimbursement->approved_by = Auth::user()->employee->id;
        $reimbursement->approved_at = now();

        if ($request->has('admin_notes')) {
            $reimbursement->admin_notes = $request->admin_notes;
        }

        $reimbursement->save();

        if ($request->expectsJson() || $request->is('api/*')) {
            return response()->json([
                'success' => true,
                'message' => 'Status reimbursement berhasil diperbarui',
                'data' => $reimbursement
            ]);
        }

        return redirect()->back()->with('success', 'Status reimbursement berhasil diperbarui');
    }
}
