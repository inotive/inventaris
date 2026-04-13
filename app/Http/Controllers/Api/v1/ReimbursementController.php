<?php

namespace App\Http\Controllers\Api\v1;

use App\Helpers\ResponseFormatter;
use App\Http\Controllers\Controller;
use App\Models\Reimbursement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;

class ReimbursementController extends Controller
{
    /**
     * List reimbursements for the authenticated user, with optional filters.
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $status = $request->string('status')->toString();
        $month = $request->string('month')->toString(); // supports 'MM' or 'YYYY-MM'
        $year = $request->string('year')->toString(); // YYYY

        // Normalize month/year if client sends 'YYYY-MM'
        if ($month && str_contains($month, '-')) {
            [$yy, $mm] = explode('-', $month, 2);
            if (!$year) { $year = $yy; }
            $month = $mm; // use MM for whereMonth
        }

        $reimbursements = Reimbursement::where('employee_id', optional($user->employee)->id)
            ->when($status, function ($query, $status) {
                $query->where('status', $status);
            })
            ->when($month, fn($query, $m) => $query->whereMonth('event_date', $m))
            ->when($year, fn($query, $y) => $query->whereYear('event_date', $y))
            ->orderByDesc('created_at')
            ->paginate(15)->withQueryString();


        return ResponseFormatter::success($reimbursements->load('approvedBy'));
    }

    /**
     * Store a new reimbursement.
     */
    public function store(Request $request)
    {
        $user = Auth::user();
        if (!$user) return ResponseFormatter::error('Unauthenticated', 401);
        $employeeId = optional($user->employee)->id;
        if (!$employeeId) return ResponseFormatter::error('User has no employee profile', 422);

        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'event_date' => ['nullable', 'date'],
            'amount' => ['required', 'numeric', 'min:0'],
            'currency' => ['nullable', 'string', 'max:10'],
            'attachment' => ['nullable', 'file', 'max:10240'], // 10MB
        ]);

        $path = null;
        if ($request->hasFile('attachment')) {
            $path = $request->file('attachment')->store('reimbursements', 'public');
        }

        $r = Reimbursement::create([
            'employee_id' => $employeeId,
            'title' => $data['title'],
            'description' => $data['description'] ?? null,
            'event_date' => $data['event_date'] ?? null,
            'amount' => $data['amount'],
            'currency' => $data['currency'] ?? 'IDR',
            'attachment_path' => $path,
            'status' => 'pending',
        ]);

        return ResponseFormatter::success($r->fresh(), 'Reimbursement created');
    }

    /**
     * Show a single reimbursement (must belong to requester).
     */
    public function show(Reimbursement $reimbursement)
    {

        $user = Auth::user();
        if (!$user) return ResponseFormatter::error('Unauthenticated', 401);
        if ($reimbursement->employee_id !== optional($user->employee)->id) {
            return ResponseFormatter::error('Forbidden', 403);
        }
        return ResponseFormatter::success($reimbursement->load('approvedBy:id,name'));
    }

    /**
     * Update a reimbursement (only pending can be edited).
     */
    public function update(Request $request, Reimbursement $reimbursement)
    {
        $user = Auth::user();
        if (!$user) return ResponseFormatter::error('Unauthenticated', 401);

        $employeeId = optional($user->employee)->id;
        if (!$employeeId) return ResponseFormatter::error('User has no employee profile', 422);

        if ($reimbursement->employee_id !== $employeeId) {
            return ResponseFormatter::error('Forbidden', 403);
        }

        if ($reimbursement->status !== 'pending') {
            return ResponseFormatter::error('Only pending reimbursement can be edited', 400);
        }

        $data = $request->validate([
            'title' => ['sometimes', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'event_date' => ['nullable', 'date'],
            'amount' => ['sometimes', 'numeric', 'min:0'],
            'currency' => ['nullable', 'string', 'max:10'],
            'attachment' => ['nullable', 'file', 'max:10240'], // 10MB
        ]);

        $updateData = [];

        if (isset($data['title'])) {
            $updateData['title'] = $data['title'];
        }
        if (isset($data['description'])) {
            $updateData['description'] = $data['description'];
        }
        if (isset($data['event_date'])) {
            $updateData['event_date'] = $data['event_date'];
        }
        if (isset($data['amount'])) {
            $updateData['amount'] = $data['amount'];
        }
        if (isset($data['currency'])) {
            $updateData['currency'] = $data['currency'];
        }

        // Handle file upload
        if ($request->hasFile('attachment')) {
            // Delete old attachment if exists
            if ($reimbursement->attachment_path) {
                Storage::disk('public')->delete($reimbursement->attachment_path);
            }
            $updateData['attachment_path'] = $request->file('attachment')->store('reimbursements', 'public');
        }

        $reimbursement->update($updateData);

        return ResponseFormatter::success($reimbursement->fresh(), 'Reimbursement updated successfully');
    }

    /**
     * Cancel a reimbursement (only pending can be canceled).
     */
    public function cancel(Request $request, Reimbursement $reimbursement)
    {
        $user = Auth::user();
        if (!$user) return ResponseFormatter::error('Unauthenticated', 401);

        $employeeId = optional($user->employee)->id;
        if (!$employeeId) return ResponseFormatter::error('User has no employee profile', 422);

        if ($reimbursement->employee_id !== $employeeId) {
            return ResponseFormatter::error('You are not allowed to cancel this reimbursement', 403);
        }

        if ($reimbursement->status !== 'pending') {
            return ResponseFormatter::error('Only pending reimbursement can be canceled', 400);
        }

        $reimbursement->update([
            'status' => 'cancelled',
            'approved_by' => $employeeId,
            'approved_at' => now(),
        ]);

        // Send notification to staff about cancellation
        try {
            $notificationService = app(\App\Services\NotificationService::class);
            $notificationService->notifyStaffOnFeedback(
                'reimbursement',
                $reimbursement->id,
                $reimbursement->employee->user_id ?? null,
                'cancelled',
                ['note' => $request->string('note')->toString() ?? '']
            );
        } catch (\Throwable $e) {
            // Silent fail – notification should not block API
        }

        return ResponseFormatter::success($reimbursement->fresh(), 'Reimbursement canceled successfully');
    }
}
