<?php

namespace App\Models;

use App\Enums\SubmissionStatusEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GeneralSubmission extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id',
        'branch_id',
        'title',
        'note',
        'tag',
        'admin_notes',
        'status',
        'approved_by',
        'approved_at',
    ];

    protected $guarded = ['id'];

    protected $casts = [
        'approved_at' => 'datetime',
        'tag' => 'array',
    ];


    const STATUS_APPROVED = 'approved';
    const STATUS_REJECTED = 'rejected';
    const STATUS_PENDING = 'pending';
    const STATUS_CANCELLED = 'cancelled';

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function attachments()
    {
        return $this->hasMany(GeneralSubmissionAttachment::class);
    }

    protected static function boot()
    {
        parent::boot();

        static::saved(fn() => \Illuminate\Support\Facades\Cache::flush());
        static::deleted(fn() => \Illuminate\Support\Facades\Cache::flush());
    }
}
