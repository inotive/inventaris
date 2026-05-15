<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Enums\EmployeeOvertimeStatusEnum;

class EmployeeOvertime extends Model
{
    use HasFactory;

    protected $table = 'employee_overtimes';

    protected $fillable = [
        'employee_id',
        'date',
        'start_time',
        'end_time',
        'duration_hours',
        'reason',
        'status',
        'approved_by',
        'notes',
    ];

    protected $casts = [
        'status' => EmployeeOvertimeStatusEnum::class,
        'duration_hours' => 'integer',
        'employee_id' => 'integer',
        'approved_by' => 'integer',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employee_id');
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
