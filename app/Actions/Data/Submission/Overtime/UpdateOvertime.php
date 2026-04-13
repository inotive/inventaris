<?php

namespace App\Actions\Data\Submission\Overtime;

use App\Models\EmployeeOvertime;
use Illuminate\Support\Facades\Auth;

class UpdateOvertime
{
    /**
     * Update overtime submission.
     *
     * @param array $data
     * @param EmployeeOvertime $overtime
     * @return void
     */
    public function execute($data, $overtime)
    {
        $overtime->update([
            'status' => $data['status'],
            'notes' => $data['admin_notes'] ?? $overtime->notes,
            'approved_by' => Auth::user()->employee->id,
            'approved_at' => now(),
        ]);
    }
}
