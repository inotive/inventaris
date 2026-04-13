<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ItemStock extends Model
{
    use HasFactory;

    protected $fillable = [
        'item_id',
        'type',
        'source_type',
        'source_id',
        'initial_stock',
        'amount',
        'last_stock',
        'note',
        'tanggal',
    ];

    public function item()
    {
        return $this->belongsTo(Item::class, 'item_id');
    }

    public function source()
    {
        return $this->morphTo();
    }

    public function getReferenceNoAttribute()
    {
        if (! $this->relationLoaded('source')) {
            $this->load('source');
        }

        switch ($this->source_type) {
            case PurchaseOrder::class:
                return $this->source?->order_no;
            case GoodTransfer::class:
                return $this->source?->transfer_no;
            case GoodIssue::class:
                return $this->source?->issue_no;
            case GoodReceipt::class:
                return $this->source?->receipt_no;
            default:
                return null;
        }
    }

    public function getTransactionValueAttribute()
    {
        return $this->amount * $this->item->price;
    }

    public function getTransactionTypeAttribute()
    {
        return $this->type === 'in' ? 'Masuk' : 'Keluar';
    }
}
