<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GoodTransferItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'good_transfer_id',
        'item_id',
        'quantity_transferred',
        'quantity_received',
        'note_received',
        'note',
    ];

    public function goodTransfer()
    {
        return $this->belongsTo(GoodTransfer::class);
    }

    public function item()
    {
        return $this->belongsTo(Item::class);
    }
}
