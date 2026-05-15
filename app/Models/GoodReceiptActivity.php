<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GoodReceiptActivity extends Model
{
    use HasFactory;

    protected $fillable = [
        'good_receipt_id',
        'title',
        'description',
        'created_by',
    ];

    public function goodReceipt()
    {
        return $this->belongsTo(GoodReceipt::class);
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}

