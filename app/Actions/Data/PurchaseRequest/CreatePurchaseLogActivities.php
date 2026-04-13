<?php

namespace App\Actions\Data\PurchaseRequest;

use App\Models\DailyReport;
use App\Models\PurchaseRequestActivity;
use App\Models\PurchaseRequest;
use App\Services\NotificationService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CreatePurchaseLogActivities
{
    public function execute($purchaseRequestId, $title, $description)
    {
        PurchaseRequestActivity::create([
            'purchase_request_id' => $purchaseRequestId,
            'title' => $title,
            'description' => $description,
            'created_by' => Auth::id(),
        ]);
    }
}
