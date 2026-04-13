<?php

namespace App\Actions\Data\Submission\DailyReport;

use App\Models\DailyReport;

class GetDailyReportById
{
    public function execute($id)
    {
        $dailyReport = DailyReport::with(['employee.branch', 'employee.department', 'approved'])
            ->findOrFail($id);

        return $dailyReport;
    }
}
