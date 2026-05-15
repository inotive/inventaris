<?php

namespace App\Actions\Data\Submission\DailyReport;

use App\Models\DailyReport;

class DeleteDailyReport
{
    public function execute(DailyReport $dailyReport)
    {
        $dailyReport->delete();

        return true;
    }
}
