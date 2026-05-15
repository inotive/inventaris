<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReceivablePayment extends Model
{
    protected $fillable = [
        'receivable_id',
        'paid_date',
        'amount',
    ];

    public function receivable()
    {
        return $this->belongsTo(Receivable::class);
    }
}
