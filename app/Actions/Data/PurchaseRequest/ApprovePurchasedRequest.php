<?php

namespace App\Actions\Data\PurchaseRequest;

use App\Models\DailyReport;
use App\Models\PurchaseOrder;
use App\Models\PurchaseRequest;
use App\Services\NotificationService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ApprovePurchasedRequest
{
    public function execute(PurchaseRequest $purchaseRequest, $data)
    {
        DB::transaction(function () use ($data, $purchaseRequest) {
            // Update items with approved quantities
            $isPartial = false;
            foreach ($data['approvals'] as $approval) {
                $item = $purchaseRequest->items()->where('id', $approval['id'])->first();
                if ($item) {
                    // Check if approved quantity is less than requested
                    if ($approval['quantity_approved'] < $item->quantity_requested) {
                        $isPartial = true;
                    }
                    $item->update([
                        'quantity_approved' => $approval['quantity_approved'],
                        'note' => $approval['note'],
                    ]);
                }
            }

            // Update status to approved or partial_approved
            $status = $isPartial ? 'partial_approved' : 'approved';

            $purchaseRequest->update([
                'status' => $status,
                'approved_at' => now(),
                'approved_by' => Auth::user()->id,
            ]);

            // activity log
            app(CreatePurchaseLogActivities::class)->execute($purchaseRequest->id, 'Menyetujui Permintaan', 'Permintaan disetujui beserta jumlah yang disetujui. Status: ' . $status);
            $purchaseRequest->refresh();
        });
    }

}
