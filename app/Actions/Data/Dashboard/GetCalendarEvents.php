<?php

namespace App\Actions\Data\Dashboard;

use App\Models\EmployeeDayOff;

class GetCalendarEvents
{

    public function execute()
    {

        $calender = EmployeeDayOff::select('name', 'date')
            ->distinct('name')
            ->get()
            ->map(function ($item) {
                return [
                    'title' => $item->name,
                    'date' => $item->date,
                    'type' => 'event'
                ];
            });

        return $calender;
    }

}
