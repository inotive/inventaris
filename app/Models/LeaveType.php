<?php

namespace App\Models;

use App\Enums\LeaveTypeCategoryEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LeaveType extends Model
{
    use HasFactory;

    protected $table = 'leave_types';

    protected $fillable = [
        'name',
        'category',
        'leave_quota_per_year',
        'description',
    ];

    protected $casts = [
        'category' => LeaveTypeCategoryEnum::class,
        'leave_quota_per_year' => 'integer',
    ];

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    /**
     * Leave requests that use this type
     */
    public function leaveRequests(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(EmployeeLeaveRequest::class, 'leave_type_id');
    }

    /**
     * Leave balances associated with this type
     */
    public function leaveBalances(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(EmployeeLeaveBalance::class, 'leave_type_id');
    }
}
