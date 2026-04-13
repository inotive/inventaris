<?php

namespace App\Http\Controllers\Api\v1;

use App\Actions\Data\MaterialRequest\ApproveMaterialRequest;
use App\Actions\Data\Submission\Procurement\CreateProcurementRequest;
use App\Actions\Data\Submission\Procurement\UpdateProcurementRequest;
use App\Http\Controllers\Controller;
use App\Helpers\ResponseFormatter;
use App\Http\Requests\ApproveProcurement;
use App\Http\Requests\Submission\ProcurementCreateRequest as SubmissionProcurementCreateRequest;
use App\Http\Requests\Submission\ProcurementUpdateRequest as SubmissionProcurementUpdateRequest;
use App\Models\MaterialRequest;
use App\Models\MaterialRequestActivity;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class SubmissionProcurementController extends Controller
{
    /**
     * Get procurement detail
     *
     * @param int $materialId
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($materialId)
    {
        $procurement = \App\Models\MaterialRequest::with([
            'requester:id,name,branch_id,department_id',
            'requester.branch:id,name',
            'requester.department:id,name',
            'approver:id,name',
            'department:id,name',
            'items.item:id,code,name',
            'items.item.unit:id,name,short_name'
        ])->find($materialId);

        if (!$procurement) {
            return ResponseFormatter::error('Procurement submission not found', 404);
        }

        $data = [
            'id' => $procurement->id,
            'request_no' => $procurement->request_no ?? null,
            'requested_at' => $procurement->requested_at,
            'approved_at' => $procurement->approved_at,
            'status' => $procurement->status,
            'requirement' => $procurement->requirement,
            'note' => $procurement->note,
            'requested_by' => [
                'id' => $procurement->requester->id,
                'name' => $procurement->requester->name,
                'branch' => $procurement->requester->branch->name ?? null,
                'department' => $procurement->requester->department->name ?? null,
            ],
            'department' => [
                'id' => $procurement->department->id ?? null,
                'name' => $procurement->department->name ?? null,
            ],
            'items' => $procurement->items->map(function ($item) {
                return [
                    'id' => $item->id,
                    'item_id' => $item->item_id,
                    'item_code' => $item->item->code ?? null,
                    'item_name' => $item->item->name ?? null,
                    'unit' => $item->item->unit->short_name ?? $item->item->unit->name ?? null,
                    'quantity_requested' => (int) $item->quantity_requested,
                    'quantity_approved' => (int) $item->quantity_approved,
                    'note' => $item->note,
                ];
            }),
            'approved_by' => [
                'id' => $procurement->approver->id ?? null,
                'name' => $procurement->approver->name ?? null,
            ],
            'created_at' => $procurement->created_at,
            'updated_at' => $procurement->updated_at,
        ];

        return ResponseFormatter::success($data, 'Procurement submission detail');
    }

    /**
     * Create procurement request
     *
     * @param SubmissionProcurementCreateRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function create(SubmissionProcurementCreateRequest $request)
    {
        $data = app(CreateProcurementRequest::class)->execute(Auth::user(), $request->validated());

        return ResponseFormatter::success($data, 'Data submission procurement request berhasil dibuat');
    }
    /**
     * Get inspection history submitted by the current authenticated user
     * GET /v1/inspections/history
     */
    public function update(SubmissionProcurementUpdateRequest $request, $materialId)
    {
        $data = app(UpdateProcurementRequest::class)->execute($request->validated(), $materialId);
        $materialRequest = MaterialRequest::findOrFail($materialId);
        $this->logActivity($materialRequest->id, 'Memperbarui Permintaan Pembelian', "Permintaan Pembelian {$materialRequest->request_no} diubah.");
        return ResponseFormatter::success(null, 'Data submission procurement request berhasil diubah');
    }

    /**
     * Approve procurement submission
     */
    public function approve($materialId)
    {
        $materialRequest = MaterialRequest::findOrFail($materialId);

        $approvals = [];
        foreach ($materialRequest->items as $item) {
            $approvals[] = [
                'id' => $item->id,
                'quantity_approved' => $item->quantity_requested,
                'quantity_requested' => $item->quantity_requested,
            ];
        }

        $data['approvals'] = $approvals;

        app(ApproveMaterialRequest::class)->execute($materialRequest, $data);

        $this->logActivity($materialRequest->id, 'Menyetujui Permintaan Pembelian', "Permintaan Pembelian {$materialRequest->request_no} disetujui.");
        return ResponseFormatter::success(null, 'Submission procurement approved');
    }

    /**
     * Reject procurement submission
     */
    public function reject(\Illuminate\Http\Request $request, $materialId)
    {
        $materialRequest = MaterialRequest::findOrFail($materialId);

        $this->logActivity($materialRequest->id, 'Menolak Permintaan Pembelian', "Permintaan Pembelian {$materialRequest->request_no} ditolak.");

        $materialRequest->update([
            'status' => 'rejected',
            'note' => $request->string('note')->toString(),
            'approved_at' => now(),
            'approved_by' => Auth::user()->employee->id,
        ]);
        return ResponseFormatter::success(null, 'Submission procurement rejected');
    }

    private function logActivity($materialRequestId, $title, $description)
    {
        try {
            MaterialRequestActivity::create([
                'material_request_id' => $materialRequestId,
                'title' => $title,
                'description' => $description,
                'created_by' => Auth::id(),
            ]);
        } catch (\Throwable $e) {
            Log::warning('Failed to log MR activity', ['mr_id' => $materialRequestId, 'error' => $e->getMessage()]);
        }
    }
}
