<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PurchaseRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'request_id',
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

    protected $casts = [
        'requested_at' => 'datetime',
        'approved_at' => 'datetime',
    ];

    public function request()
    {
        return $this->belongsTo(MaterialRequest::class);
    }

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
        return $this->hasMany(PurchaseRequestItem::class);
    }

    public function purchaseOrder()
    {
        return $this->hasOne(PurchaseOrder::class, 'request_id');
    }

    protected static function booted(): void
    {
        static::creating(function (PurchaseRequest $pr) {
            if (empty($pr->request_no)) {
                $pr->request_no = static::generateRequestNo($pr->request_at);
            }
            if (empty($pr->status)) {
                $pr->status = 'on_progress';
            }
        });
    }

    public static function generateRequestNo($requestDate = null): string
    {
        $date = $requestDate ? Carbon::parse($requestDate) : now();
        $ym = $date->format('Ym');
        $prefix = 'PR-' . $ym . '-';

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

    public function logActivities()
    {
        return $this->hasMany(PurchaseRequestActivity::class, 'purchase_request_id');
    }
}
