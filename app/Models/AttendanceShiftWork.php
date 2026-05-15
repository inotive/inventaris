<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AttendanceShiftWork extends Model
{
    protected $fillable = [
        'employee_id',
        'department_id',
        'shift_id',
        'work_date',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function shift()
    {
        return $this->belongsTo(Shift::class);
    }

    public function attendances()
    {
        return $this->hasMany(Attendance::class, 'shift_work_id');
    }
}
