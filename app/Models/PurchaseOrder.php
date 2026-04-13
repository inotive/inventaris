<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class PurchaseOrder extends Model
{
    use HasFactory;

    protected $fillable = [
        'request_id',
        'order_no',
        'ordered_by',
        'ordered_at',
        'vendor',
        'note',
        'status_invoice',
        'status_delivered',
        'receive_notes',
        'approved_by',
        'branch_id',
        'approved_at',
        'paid_amount',
    ];

    protected $casts = [
        'ordered_at' => 'datetime',
        'approved_at' => 'datetime',
        'branch_id' => 'integer',
    ];

    const STATUS_INVOICE_BELUM_DIBAYAR = 'belum_dibayar';
    const STATUS_INVOICE_LUNAS = 'lunas';
    const STATUS_INVOICE_BELUM_LUNAS = 'belum_lunas';

    const STATUS_DELIVERED = 'delivered';
    const STATUS_DELIVERED_PARTIAL = 'partial';
    const STATUS_DELIVERED_PENDING = 'pending';

    public function request()
    {
        return $this->belongsTo(PurchaseRequest::class);
    }

    public function applicant()
    {
        return $this->belongsTo(Employee::class, 'ordered_by');
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function items()
    {
        return $this->hasMany(PurchaseOrderItem::class);
    }

    public function activities()
    {
        return $this->hasMany(PurchaseOrderActivity::class);
    }


    protected static function booted(): void
    {
        static::creating(function (PurchaseOrder $po) {
            if (empty($po->order_no)) {
                $po->order_no = static::generateOrderNo($po->ordered_at);
            }
        });
    }

    public static function generateOrderNo($requestDate = null): string
    {
        $date = $requestDate ? Carbon::parse($requestDate) : now();
        $ym = $date->format('Ym');
        $prefix = 'PO-' . $ym . '-';

        $last = static::query()
            ->where('order_no', 'like', $prefix . '%')
            ->orderBy('order_no', 'desc')
            ->value('order_no');

        $seq = 1;
        if ($last) {
            $parts = explode('-', $last);
            $lastSeq = (int)($parts[2] ?? 0);
            $seq = $lastSeq + 1;
        }

        return $prefix . str_pad((string)$seq, 4, '0', STR_PAD_LEFT);
    }
}
