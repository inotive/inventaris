<?php

namespace App\Actions\Data\GoodReceipt;

use App\Models\GoodReceiptActivity;
use Illuminate\Support\Facades\Auth;

class CreateGoodReceiptLogActivities
{
    public function execute($goodReceiptId, $title, $description)
    {
        GoodReceiptActivity::create([
            'good_receipt_id' => $goodReceiptId,
            'title' => $title,
            'description' => $description,
            'created_by' => Auth::id(),
        ]);
    }
}

