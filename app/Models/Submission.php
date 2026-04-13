<?php

namespace App\Models;

use App\Enums\SubmissionStatusEnum;
use App\Enums\SubmissionTypeEnum;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Submission extends Model
{
    protected $table = 'submissions';

    protected $fillable = [
        'employee_id',
        'branch_id',
        'submission_number',
        'submission_date',
        'start_date',
        'end_date',
        'type',
        'status',
        'notes',
        'approved_by',
        'approved_at',
        'rejected_by',
        'rejected_at',
        'rejected_reason',
        'cancelled_by',
        'cancelled_at',
        'cancelled_reason',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'status' => SubmissionStatusEnum::class,
        'type'   => SubmissionTypeEnum::class,
    ];

    //constans type
    public const TYPES = [
        'submissions' => "Semua Pengajuan",
        'sick' => "Izin Sakit",
        'annual-leave' => "Izin Cuti",
        'others' => "Izin Lainnya",
        'overtime' => "Lembur",
        'debt' => "Piutang",
        'reimbursement' => "Reimbursement",
        'general' => "Pengajuan Umum",
        'employee' => "Karyawan",
    ];


    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }
}
