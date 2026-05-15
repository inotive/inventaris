<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GoodReceiptItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'good_receipt_id',
        'item_id',
        'quantity_received',
        'quantity_transferred',
        'note_transferred',
        'note',
    ];

    public function goodReceipt()
    {
        return $this->belongsTo(GoodReceipt::class);
    }

    public function item()
    {
        return $this->belongsTo(Item::class);
    }
}
