<?php

namespace App\Actions\Data\PurchaseOrder;

use App\Models\PurchaseOrderActivity;
use Illuminate\Support\Facades\Auth;

class CreatePurchaseOrderLogActivities
{
    public function execute($purchaseOrderId, $title, $description)
    {
        PurchaseOrderActivity::create([
            'purchase_order_id' => $purchaseOrderId,
            'title' => $title,
            'description' => $description,
            'created_by' => Auth::id(),
        ]);
    }
}
