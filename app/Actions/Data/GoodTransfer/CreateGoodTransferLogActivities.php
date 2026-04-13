<?php

namespace App\Actions\Data\GoodTransfer;

use App\Models\GoodTransferActivity;
use Illuminate\Support\Facades\Auth;

class CreateGoodTransferLogActivities
{
    public function execute($goodTransferId, $title, $description)
    {
        GoodTransferActivity::create([
            'good_transfer_id' => $goodTransferId,
            'title' => $title,
            'description' => $description,
            'created_by' => Auth::id(),
        ]);
    }
}
