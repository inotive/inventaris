<?php

namespace App\Http\Controllers\Api\v1;

use App\Actions\Data\GoodIssue\ApproveGoodIssue;
use App\Actions\Data\Submission\Usage\CreateUsageRequest;
use App\Actions\Data\Submission\Usage\GetDetailSubmission;
use App\Actions\Data\Submission\Usage\GetListUsageRequest;
use App\Actions\Data\Submission\Usage\UpdateUsageRequest;
use App\Http\Controllers\Controller;
use App\Helpers\ResponseFormatter;
use App\Http\Requests\Submission\CreateUsageRequest as SubmissionCreateUsageRequest;
use App\Http\Requests\Submission\UpdateUsageRequest as SubmissionUpdateUsageRequest;
use App\Http\Requests\Submission\UsageListRequest;
use App\Http\Resources\Usage\UsageListResource;
use App\Http\Resources\Usage\UsageResource;
use App\Models\GoodIssue;
use App\Models\GoodIssueActivity;
use App\Models\GoodIssueItem;
use App\Models\ItemStock;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SubmissionUsageController extends Controller
{
    /**
     * Get usage detail
     *
     * @param int $usageId
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($usageId)
    {
        $goodIssue = \App\Models\GoodIssue::with([
            'requestBy:id,name,branch_id,department_id',
            'requestBy.branch:id,name',
            'requestBy.department:id,name',
            'approvedBy:id,name',
            'department:id,name',
            'items.item:id,code,name',
            'items.item.unit:id,name,short_name'
        ])->find($usageId);

        if (!$goodIssue) {
            return ResponseFormatter::error('Usage submission not found', 404);
        }

        $data = [
            'id' => $goodIssue->id,
            'request_no' => $goodIssue->request_no ?? null,
            'kode_usage' => $goodIssue->kode_usage ?? null,
            'date' => $goodIssue->date,
            'status' => $goodIssue->status,
            'requirement' => $goodIssue->requirement,
            'note' => $goodIssue->note,
            'request_by' => [
                'id' => $goodIssue->requestBy->id,
                'name' => $goodIssue->requestBy->name,
                'branch' => $goodIssue->requestBy->branch->name ?? null,
                'department' => $goodIssue->requestBy->department->name ?? null,
            ],
            'department' => [
                'id' => $goodIssue->department->id ?? null,
                'name' => $goodIssue->department->name ?? null,
            ],
            'items' => $goodIssue->items->map(function ($item) {
                return [
                    'id' => $item->id,
                    'item_id' => $item->item_id,
                    'item_code' => $item->item->code ?? null,
                    'item_name' => $item->item->name ?? null,
                    'unit' => $item->item->unit->short_name ?? $item->item->unit->name ?? null,
                    'quantity_issued' => (int) $item->quantity_issued,
                    'quantity_approved' => (int) $item->quantity_approved,
                    'note_received' => $item->note_received,
                ];
            }),
            'approved_by' => [
                'id' => $goodIssue->approvedBy->id ?? null,
                'name' => $goodIssue->approvedBy->name ?? null,
            ],
            'approved_at' => $goodIssue->approved_at,
            'created_at' => $goodIssue->created_at,
            'updated_at' => $goodIssue->updated_at,
        ];

        return ResponseFormatter::success($data, 'Usage submission detail');
    }

    /**
     * Create usage request
     *
     * @param SubmissionCreateUsageRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function create(SubmissionCreateUsageRequest $request)
    {
        $data = app(CreateUsageRequest::class)->execute(Auth::user(), $request->validated());
        return ResponseFormatter::success(null, 'Data submission usage request berhasil dibuat');
    }

    /**
     * Get inspection history submitted by the current authenticated user
     * GET /v1/inspections/history
     */
    public function update(SubmissionUpdateUsageRequest $request, $usageId)
    {
        $data = app(UpdateUsageRequest::class)->execute($request->validated(), $usageId);
        return ResponseFormatter::success(null, 'Data submission usage request berhasil diubah');
    }

    /**
     * Approve a usage submission
     */
    public function approve(Request $request, $usageId)
    {
        Log::info('Approve usage', ['request' => $request->all()]);
        $payload = [
            'status' => 'approved',
        ];
        if ($request->filled('note')) {
            $payload['note'] = $request->string('note')->toString();
        }

        $goodIssue = GoodIssue::findOrFail($usageId);

        if ($goodIssue->status == 'approved') {
            return ResponseFormatter::error('Pemakaian Barang sudah disetujui sebelumnya.', 400);
        }

        $result = app(ApproveGoodIssue::class)->execute($goodIssue, $payload);

        $this->logActivity($goodIssue->id, 'Menyetujui Pemakaian Barang', "Pemakaian Barang {$goodIssue->kode_usage} disetujui.");

        // Send notification to staff about approval
        if ($result && isset($result['goodIssue'])) {
            $notificationService = app(\App\Services\NotificationService::class);
            $notificationService->notifyStaffOnFeedback(
                'usage',
                $result['goodIssue']->id,
                $result['goodIssue']->request_by,
                'approved',
                ['note' => $payload['note'] ?? '']
            );
        }

        return ResponseFormatter::success(null, 'Submission usage approved');
    }

    /**
     * Reject a usage submission
     */
    public function reject(Request $request, $usageId)
    {
        Log::info('Reject usage', ['request' => $request->all()]);
        $payload = [
            'status' => 'rejected',
        ];
        if ($request->filled('note')) {
            $payload['note'] = $request->string('note')->toString();
        }

        $result = app(UpdateUsageRequest::class)->execute($payload, $usageId);

        // Send notification to staff about rejection
        if ($result && isset($result['goodIssue'])) {
            $notificationService = app(\App\Services\NotificationService::class);
            $notificationService->notifyStaffOnFeedback(
                'usage',
                $result['goodIssue']->id,
                $result['goodIssue']->request_by,
                'rejected',
                ['note' => $payload['note'] ?? '']
            );
        }

        return ResponseFormatter::success(null, 'Submission usage rejected');
    }


    public function approveMobile(Request $request, $usageId)
    {
        Log::info('Approve mobile usage', ['request' => $request->all()]);
        $payload = [
            'status' => 'approved',
        ];
        if ($request->filled('note')) {
            $payload['note'] = $request->string('note')->toString();
        }

        try {
            DB::transaction(function () use ($usageId, $payload) {
                $goodIssueItem = GoodIssueItem::with('item')->findOrFail($usageId);
                $quantityApproved = $goodIssueItem->quantity_issued;

                // Validate stock availability
                $lastLogItemStock = ItemStock::where('item_id', $goodIssueItem->item_id)
                    ->latest('id')
                    ->first();

                $currentStock = $lastLogItemStock?->last_stock ?? 0;

                if ($quantityApproved > $currentStock) {
                    $itemName = $goodIssueItem->item?->name ?? 'Unknown';
                    throw new \Exception("Jumlah yang disetujui ({$quantityApproved}) melebihi stok yang tersedia ({$currentStock}) untuk item {$itemName}.");
                }

                // Update good issue item
                $goodIssueItem->update([
                    'quantity_approved' => $quantityApproved,
                    'note_received' => $payload['note'] ?? null,
                ]);


                // Update good issue status
                $update = $goodIssueItem->goodIssue()->update([
                    'status' => 'approved',
                    'approved_at' => now(),
                    'approved_by' => Auth::user()->id,
                ]);


                // Update stock
                $initialStock = $lastLogItemStock?->last_stock ?? 0;
                $lastStock = $initialStock - $quantityApproved;

                ItemStock::create([
                    'item_id' => $goodIssueItem->item_id,
                    'type' => 'Out',
                    'source_type' => GoodIssue::class,
                    'source_id' => $goodIssueItem->goodIssue->id,
                    'initial_stock' => $initialStock,
                    'amount' => $quantityApproved,
                    'last_stock' => $lastStock,
                ]);

                // Log activity
                $this->logActivity($goodIssueItem->goodIssue->id, 'Menyetujui Pemakaian Barang', "Pemakaian Barang {$goodIssueItem->goodIssue->kode_usage} disetujui.");

                // Send notification to staff about approval
                $notificationService = app(\App\Services\NotificationService::class);
                $notificationService->notifyStaffOnFeedback(
                    'usage',
                    $goodIssueItem->goodIssue->id,
                    $goodIssueItem->goodIssue->request_by,
                    'approved',
                    ['note' => $payload['note'] ?? '']
                );
            });

            return ResponseFormatter::success(null, 'Submission usage approved');
        } catch (\Exception $e) {

            dd($e->getMessage());
            Log::error('Failed to approve mobile usage', [
                'error' => $e->getMessage(),
                'usage_id' => $usageId,
                'trace' => $e->getTraceAsString(),
            ]);
            return ResponseFormatter::error($e->getMessage(), 400);
        }
    }

    public function rejectMobile(Request $request, $usageId)
    {
        Log::info('Reject mobile usage', ['request' => $request->all()]);
        $notes = $request->filled('note') ? $request->string('note')->toString() : null;

        try {
            DB::transaction(function () use ($usageId, $notes) {
                $goodIssue = GoodIssue::findOrFail($usageId);

                // Update good issue item
                $goodIssue->update([
                    'status' => 'rejected',
                    'notes' => $notes,
                    'approved_by' => Auth::id(),
                    'approved_at' => now(),
                ]);

                // Log activity
                $this->logActivity($goodIssue->id, 'Menolak Pemakaian Barang', "Pemakaian Barang {$goodIssue->kode_usage} ditolak.");

                // Send notification to staff about rejection
                try {
                    $notificationService = app(\App\Services\NotificationService::class);
                    $notificationService->notifyStaffOnFeedback(
                        'usage',
                        $goodIssue->id,
                        $goodIssue->request_by,
                        'rejected',
                        ['note' => $notes ?? '']
                    );
                } catch (\Throwable $e) {
                    Log::error('Failed to send rejection notification', [
                        'error' => $e->getMessage(),
                        'good_issue_id' => $goodIssue->id,
                    ]);
                }
            });

            return ResponseFormatter::success(null, 'Submission usage rejected');
        } catch (\Exception $e) {
            Log::error('Failed to reject mobile usage', [
                'error' => $e->getMessage(),
                'usage_id' => $usageId,
                'trace' => $e->getTraceAsString(),
            ]);
            return ResponseFormatter::error($e->getMessage(), 400);
        }
    }

    private function logActivity($goodIssueId, $title, $description)
    {
        try {
            GoodIssueActivity::create([
                'good_issue_id' => $goodIssueId,
                'title' => $title,
                'description' => $description,
                'created_by' => Auth::id(),
            ]);
        } catch (\Throwable $e) {
            Log::warning('Failed to log Pemakaian Barang activity', [
                'good_issue_id' => $goodIssueId,
                'error' => $e->getMessage()
            ]);
        }
    }

}
