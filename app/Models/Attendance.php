<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Attendance extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'employee_id',
        'department_id',
        'shift_id',
        'tanggal',
        'jam_masuk',
        'late_duration_minutes',
        'jam_keluar',
        'status',
        'keterangan',
        'shift_work_id',
        'is_face_correct',
    ];

    protected $casts = [
        'is_face_correct' => 'integer',
    ];

    public const STATUS_RUNNING = 'RUNNING';
    public const STATUS_COMPLETE = 'COMPLETE';
    public const STATUS_LEAVE = 'CUTI';
    public const STATUS_SICK = 'SAKIT';
    public const STATUS_ALFA = 'ABSEN';
    public const STATUS_PERMIT = 'IZIN';


    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function shift()
    {
        return $this->belongsTo(Shift::class);
    }

    public function shiftWork()
    {
        return $this->belongsTo(AttendanceShiftWork::class, 'shift_work_id');
    }
}
