<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class MaterialRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'request_no',
        'department_id',
        'requested_by',
        'requested_at',
        'approved_by',
        'approved_at',
        'requirement',
        'note',
        'status',
    ];

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function requester()
    {
        return $this->belongsTo(Employee::class, 'requested_by');
    }

    public function approver()
    {
        return $this->belongsTo(Employee::class, 'approved_by');
    }

    public function items()
    {
        return $this->hasMany(MaterialRequestItem::class);
    }

    public function purchaseRequest()
    {
        return $this->hasMany(PurchaseRequest::class, 'request_id');
    }

    public function goodIssues()
    {
        return $this->hasMany(GoodIssue::class, 'request_id');
    }

    protected static function booted(): void
    {
        static::creating(function (MaterialRequest $mr) {
            if (empty($mr->request_no)) {
                $mr->request_no = static::generateRequestNo($mr->request_at);
            }
            if (empty($mr->status)) {
                $mr->status = 'on_progress';
            }
        });
    }

    public static function generateRequestNo($requestDate = null): string
    {
        $date = $requestDate ? Carbon::parse($requestDate) : now();
        $ym = $date->format('Ym');
        $prefix = 'MR-' . $ym . '-';

        $last = static::query()
            ->where('request_no', 'like', $prefix . '%')
            ->orderBy('request_no', 'desc')
            ->value('request_no');

        $seq = 1;
        if ($last) {
            $parts = explode('-', $last);
            $lastSeq = (int)($parts[2] ?? 0);
            $seq = $lastSeq + 1;
        }

        return $prefix . str_pad((string)$seq, 4, '0', STR_PAD_LEFT);
    }
}
