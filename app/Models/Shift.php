<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Shift extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'name',
        'start_time',
        'end_time',
        'late_tolerance',
        'overtime_start',
        'weekly_pattern',
    ];

    protected $casts = [
        'start_time' => 'datetime:H:i',
        'end_time'   => 'datetime:H:i',
        'late_tolerance' => 'datetime:H:i',
        'overtime_start' => 'datetime:H:i',
        'weekly_pattern' => 'array',
    ];

    public function attendances()
    {
        return $this->hasMany(Attendance::class);
    }

    public function attendanceShiftWorks()
    {
        return $this->hasMany(AttendanceShiftWork::class);
    }
}
