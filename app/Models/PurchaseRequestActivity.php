<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PurchaseRequestActivity extends Model
{
    protected $fillable = [
        'purchase_request_id',
        'title',
        'description',
        'created_by',
    ];

    public function purchaseRequest()
    {
        return $this->belongsTo(PurchaseRequest::class);
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
