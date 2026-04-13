<?php

namespace App\Actions\Data\Dashboard;

use App\Models\Submission;
use App\Models\LogActivity;

class GetTodayActivities
{
    public function execute($today)
    {
        // Count log activities today
        $logActivitiesToday = LogActivity::whereDate('created_at', $today)->count();

        return $logActivitiesToday;
    }
}
