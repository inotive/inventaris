<?php

namespace App\Actions\Data\MaterialRequest;

use App\Models\MaterialRequest;
use App\Models\PurchaseRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ApproveMaterialRequest
{
    public function execute(MaterialRequest $materialRequest, $data)
    {
        DB::beginTransaction();
        try {
            foreach ($data['approvals'] as $ap) {
                if (!isset($ap['id'])) continue;
                $item = $materialRequest->items()->where('id', $ap['id'])->first();
                if (!$item) continue;
                $qty = max(0, (int)($ap['quantity_approved'] ?? 0));
                $item->quantity_approved = $qty;
                if (isset($ap['note'])) $item->note = $ap['note'];
                $item->save();
            }

            $materialRequest->update([
                'status' => 'approved',
                'approved_at' => now(),
                'approved_by' => Auth::user()->employee->id,
            ]);

            // $this->createPurchaseRequest($materialRequest);

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }



    private function createPurchaseRequest(MaterialRequest $materialRequest)
    {
        $requestNo = PurchaseRequest::generateRequestNo($materialRequest->requested_at);
        $purchaseRequest = $materialRequest->purchaseRequest()->create([
            'request_no' => $requestNo,
            'requested_at' => now(),
            'requested_by' => $materialRequest->requested_by,
            'department_id' => $materialRequest->department_id,
            'requirement' => $materialRequest->requirement,
        ]);

        foreach ($materialRequest->items as $item) {
            $purchaseRequest->items()->create([
                'item_id' => $item->item_id,
                'quantity_requested' => $item->quantity_approved,
                'note' => $item->note,
            ]);
        }
    }
}
