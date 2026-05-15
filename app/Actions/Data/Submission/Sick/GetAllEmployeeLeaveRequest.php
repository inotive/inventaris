<?php

namespace App\Actions\Data\Submission\Sick;

use App\Enums\SubmissionStatusEnum;
use App\Models\EmployeeLeaveRequest;
use App\Models\Submission;
use Illuminate\Support\Facades\Auth;

class GetAllEmployeeLeaveRequest
{
    /**
     * Get all employee leave request.
     *
     * @param string|null $type
     * @return array
     */
    public function execute($leaveType)
    {
        $isSuperAdmin = Auth::user()->hasRole('Superadmin');

        $data = EmployeeLeaveRequest::with(['employee', 'employee.branch', 'leaveType', 'approver'])
            ->latest()
            ->whereIn('leave_type_id', $leaveType)
            ->when(!$isSuperAdmin && Auth::user()->employee->branch_id != 2, function ($query) {
                $query->whereHas('employee', function ($q) {
                    $q->where('branch_id', Auth::user()->employee->branch_id);
                });
            })
            ->get();

        return $data;
    }
}
