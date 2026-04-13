<?php

namespace App\Actions\Data\PurchaseRequest;

use App\Models\DailyReport;
use App\Models\MaterialRequestActivity;
use App\Models\PurchaseRequest;
use App\Services\NotificationService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CreatePurchaseRequest
{
    public function execute($data)
    {
        DB::transaction(function () use ($data) {
            $purchaseRequest = PurchaseRequest::create([
                'request_id' => $data['request_id'],
                'request_no' => $data['request_no'],
                'requested_at' => $data['requested_at'],
                'requested_by' => $data['employee_id'],
                'department_id' => $data['department_id'],
                'requirement' => $data['requirement'] ?? null,
            ]);

            foreach ($data['requests'] as $r) {
                $purchaseRequest->items()->create([
                    'item_id' => $r['item_id'],
                    'quantity_requested' => $r['request'],
                    'note' => $r['note'] ?? null,
                ]);
            }


            // activity log
            app(CreatePurchaseLogActivities::class)->execute($purchaseRequest->id, 'Membuat Permintaan', 'Permintaan baru dibuat dengan nomor ' . $purchaseRequest->request_no);
        });
    }
}
