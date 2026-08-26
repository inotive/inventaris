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
        'po_no',
        'from_branch',
        'to_branch',
        'sent_by',
        'received_by',
        'purpose',
        'status',
        'transferred_at',
        'attachment_path',
        'attachment_name',
    ];

    protected $casts = [
        'transferred_at' => 'datetime',
        'attachment_path' => 'array',
        'attachment_name' => 'array',
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
        return $this->hasOne(GoodReceipt::class, 'transfer_id');
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
        $year = $date->format('Y');
        $month = (int)$date->format('n');

        $romanMonths = [
            1 => 'I', 2 => 'II', 3 => 'III', 4 => 'IV', 5 => 'V', 6 => 'VI',
            7 => 'VII', 8 => 'VIII', 9 => 'IX', 10 => 'X', 11 => 'XI', 12 => 'XII'
        ];
        $romanMonth = $romanMonths[$month] ?? 'I';

        $suffix = '/SJ-SPB/SGT/' . $romanMonth . '/' . $year;

        $last = static::query()
            ->where('transfer_no', 'like', '%' . $suffix)
            ->orderBy('transfer_no', 'desc')
            ->value('transfer_no');

        $seq = 1;
        if ($last) {
            $parts = explode('/', $last);
            $lastSeq = (int)($parts[0] ?? 0);
            $seq = $lastSeq + 1;
        }

        return str_pad((string)$seq, 3, '0', STR_PAD_LEFT) . $suffix;
    }
}
