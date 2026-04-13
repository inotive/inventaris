<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PurchaseOrderActivity extends Model
{
    protected $fillable = [
        'purchase_order_id',
        'title',
        'description',
        'created_by',
    ];

    public function purchaseOrder()
    {
        return $this->belongsTo(PurchaseOrder::class);
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
