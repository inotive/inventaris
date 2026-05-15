<?php

namespace App\Models;

use App\Enums\DailyReportEnum;
use App\Enums\SubmissionStatusEnum;
use Illuminate\Database\Eloquent\Model;

class DailyReport extends Model
{
    protected $fillable = [
        'employee_id',
        'start_date',
        'end_date',
        'name',
        'salary',
        'reason',
        'status',
        'approved_by',
        'approved_at',
        'notes',
    ];

    protected $casts = [
        'status' => DailyReportEnum::class,
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function approved()
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
