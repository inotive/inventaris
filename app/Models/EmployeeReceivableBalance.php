<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmployeeReceivableBalance extends Model
{
    protected $fillable = [
        'employee_id',
        'period_year',
        'period_month',
        'limit_amount',
        'used_amount',
        'remaining_amount',
        'policy',
    ];

    protected $casts = [
        'employee_id' => 'integer',
        'period_year' => 'integer',
        'period_month' => 'integer',
        'limit_amount' => 'integer',
        'used_amount' => 'integer',
        'remaining_amount' => 'integer',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function scopeForPeriod($query, ?int $year, ?int $month)
    {
        if (!is_null($year)) $query->where('period_year', $year);
        if (!is_null($month)) $query->where('period_month', $month);
        return $query;
    }
}
