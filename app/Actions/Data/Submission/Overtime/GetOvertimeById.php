<?php

namespace App\Actions\Data\Submission\Overtime;

use App\Models\EmployeeOvertime;

class GetOvertimeById
{
    /**
     * Get overtime by ID.
     *
     * @param int $id
     * @return EmployeeOvertime
     */
    public function execute($id)
    {
        return EmployeeOvertime::with(['employee', 'employee.branch', 'approved'])
            ->where('id', $id)
            ->firstOrFail();
    }
}
