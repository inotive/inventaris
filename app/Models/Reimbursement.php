<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Reimbursement extends Model
{
    protected $fillable = [
        'employee_id',
        'title',
        'description',
        'event_date',
        'amount',
        'currency',
        'attachment_path',
        'status',
        'approved_by',
        'approved_at',
        'admin_notes',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function approvedBy()
    {
        return $this->belongsTo(Employee::class, 'approved_by');
    }

    protected static function boot()
    {
        parent::boot();

        static::saved(fn() => \Illuminate\Support\Facades\Cache::flush());
        static::deleted(fn() => \Illuminate\Support\Facades\Cache::flush());
    }
}
