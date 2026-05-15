<?php

namespace App\Models;

use App\Enums\EmployeeLeaveApprovalStatusEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmployeeLeaveApproval extends Model
{
    use HasFactory;

    protected $table = 'employee_leave_approvals';

    protected $fillable = [
        'leave_request_id',
        'approved_id',
        'level',
        'status',
        'note',
        'approved_at',
    ];

    protected $casts = [
        'approved_at' => 'datetime',
        'approved_id' => 'integer',
        'leave_request_id' => 'integer',
        'status' => EmployeeLeaveApprovalStatusEnum::class,
    ];

    /**
     * Relasi ke permintaan cuti (parent)
     */
    public function leaveRequest(): BelongsTo
    {
        return $this->belongsTo(EmployeeLeaveRequest::class, 'leave_request_id');
    }

    /**
     * Relasi ke karyawan yang menjadi approver
     */
    public function approver(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'approved_id');
    }
}
