<?php

namespace App\Actions\Data\Submission\Sick;

use App\Enums\SubmissionStatusEnum;
use App\Models\EmployeeLeaveRequest;
use App\Models\Submission;


class GetSubmissionById
{
    /**
     * Get submission paginate based on type and status.
     *
     * @param string|null $type
     * @return array
     */
    public function execute($id, $type)
    {
        return EmployeeLeaveRequest::with(['employee', 'employee.branch', 'approvals', 'leaveType'])
            ->where('id', $id)
            ->firstOrFail();
    }
}
