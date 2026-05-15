<?php

namespace App\Models;

use App\Enums\ReceivableStatusEnum;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Receivable extends Model
{
    protected $fillable = [
        'amount',
        'date',
        'tenor',
        'note',
        'file_path',
        'status',
        'request_by',
        'approved_by',
        'approved_at',
        'admin_notes',
    ];

    protected $casts = [
        'approved_at' => 'datetime',
        'amount' => 'decimal:2',
        'status' => ReceivableStatusEnum::class,
    ];

    /**
     * Get the employee who requested the loan
     */
    public function requester(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'request_by');
    }

    /**
     * Get the employee who approved the loan
     */
    public function approver(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'approved_by');
    }

    /**
     * Get the employee who requested the loan (alias for requester)
     */
    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'request_by');
    }

    public function payments()
    {
        return $this->hasMany(ReceivablePayment::class);
    }

    protected static function boot()
    {
        parent::boot();

        static::saved(fn() => \Illuminate\Support\Facades\Cache::flush());
        static::deleted(fn() => \Illuminate\Support\Facades\Cache::flush());
    }
}
