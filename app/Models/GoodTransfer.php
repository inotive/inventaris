<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class GoodTransfer extends Model
{
    use HasFactory;

    protected $fillable = [
        'transfer_no',
        'from_branch',
        'to_branch',
        'sent_by',
        'received_by',
        'purpose',
        'status',
        'transferred_at',
    ];

    protected $casts = [
        'transferred_at' => 'datetime',
    ];

    public function fromBranch()
    {
        return $this->belongsTo(Branch::class, 'from_branch');
    }

    public function toBranch()
    {
        return $this->belongsTo(Branch::class, 'to_branch');
    }

    public function sentBy()
    {
        return $this->belongsTo(Employee::class, 'sent_by');
    }

    public function receivedBy()
    {
        return $this->belongsTo(Employee::class, 'received_by');
    }

    public function items()
    {
        return $this->hasMany(GoodTransferItem::class);
    }

    public function goodReceipt()
    {
        return $this->hasOne(GoodReceipt::class, 'good_transfer_id');
    }

    public function activities()
    {
        return $this->hasMany(GoodTransferActivity::class)->orderBy('created_at', 'desc');
    }

    protected static function booted(): void
    {
        static::creating(function (GoodTransfer $gt) {
            if (empty($gt->transfer_no)) {
                $gt->transfer_no = static::generateTransferNo($gt->transferred_at);
            }
        });
    }

    public static function generateTransferNo($requestDate = null): string
    {
        $date = $requestDate ? Carbon::parse($requestDate) : now();
        $ym = $date->format('Ym');
        $prefix = 'TF-' . $ym . '-';

        $last = static::query()
            ->where('transfer_no', 'like', $prefix . '%')
            ->orderBy('transfer_no', 'desc')
            ->value('transfer_no');

        $seq = 1;
        if ($last) {
            $parts = explode('-', $last);
            $lastSeq = (int)($parts[2] ?? 0);
            $seq = $lastSeq + 1;
        }

        return $prefix . str_pad((string)$seq, 4, '0', STR_PAD_LEFT);
    }
}
