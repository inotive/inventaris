<?php

namespace App\Models;

use App\Enums\EmployeeLeaveRequestStatusEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmployeeLeaveRequest extends Model
{
    use HasFactory;

    protected $table = 'employee_leave_requests';

    protected $fillable = [
        'employee_id',
        'leave_type_id',
        'start_date',
        'end_date',
        'total_days',
        'reason',
        'status',
        'attachment',
        'approved_by',
        'approved_at',
        'admin_notes',
        'corrected_start_date',
        'corrected_end_date',
        'corrected_by',
        'corrected_at',
        'correction_reason',
    ];

    protected $casts = [
        'employee_id' => 'integer',
        'leave_type_id' => 'integer',
        'start_date' => 'date',
        'end_date' => 'date',
        'corrected_start_date' => 'date',
        'corrected_end_date' => 'date',
        'approved_at' => 'datetime',
        'corrected_at' => 'datetime',
        'attachment' => 'array',
        'status' => EmployeeLeaveRequestStatusEnum::class,
    ];

    protected static function boot()
    {
        parent::boot();

        static::updated(fn() => Cache::flush());
        static::deleted(fn() => Cache::flush());
    }

    // === Relationships ===
    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }

    public function leaveType(): BelongsTo
    {
        return $this->belongsTo(LeaveType::class, 'leave_type_id');
    }

    /**
     * Semua approval (manager/HR) untuk request ini.
     */
    public function approvals(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'approved_by');
    }

    /**
     * Semua approval untuk request ini.
     */
    public function approver(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'approved_by');
    }

    /**
     * Relasi ke saldo cuti
     */
    public function balanceLeave(): BelongsTo
    {
        return $this->belongsTo(EmployeeLeaveBalance::class, 'balance_leave_id');
    }

    /**
     * Siapa yang melakukan koreksi tanggal
     */
    public function correctedBy(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'corrected_by');
    }
}
