<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GoodReceipt extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id',
        'order_id',
        'transfer_id',
        'source',
        'note',
        'received_at',
    ];

    protected $casts = [
        'received_at' => 'datetime',
    ];

    public function receiver()
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }

    public function order()
    {
        return $this->belongsTo(PurchaseOrder::class, 'order_id');
    }

    public function transfer()
    {
        return $this->belongsTo(GoodTransfer::class, 'transfer_id');
    }

    public function items()
    {
        return $this->hasMany(GoodReceiptItem::class);
    }

    public function activities()
    {
        return $this->hasMany(GoodReceiptActivity::class);
    }

    public static function generateRequestNo()
    {
        $year = date('Y');
        $prefix = 'GR-' . $year . '-';

        $lastNumber = static::query()
            ->where('request_no', 'like', $prefix . '%')
            ->orderBy('request_no', 'desc')
            ->value('request_no');

        if ($lastNumber) {
            $lastSequence = (int) substr($lastNumber, strlen($prefix));
            $nextSequence = $lastSequence + 1;
        } else {
            $nextSequence = 1;
        }

        return $prefix . str_pad($nextSequence, 4, '0', STR_PAD_LEFT);
    }
}
