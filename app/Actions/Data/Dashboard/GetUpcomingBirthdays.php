<?php

namespace App\Actions\Data\Dashboard;

use App\Models\Employee;
use Carbon\Carbon;

class GetUpcomingBirthdays
{
    public function execute($limit = 4)
    {
        $today = Carbon::today();
        $currentMonth = $today->month;
        $currentDay = $today->day;

        // Get employees with birthdays in the next 30 days
        $upcomingBirthdays = Employee::whereNotNull('birthdate')
            ->where('status', 'active')
            ->get()
            ->map(function ($employee) use ($today) {
                $dob = Carbon::parse($employee->birthdate);

                // Set birthday to current year
                $birthdayThisYear = Carbon::create($today->year, $dob->month, $dob->day);

                // If birthday has passed this year, use next year
                if ($birthdayThisYear->lt($today)) {
                    $birthdayThisYear->addYear();
                }

                $daysUntil = $today->diffInDays($birthdayThisYear, false);

                return [
                    'id' => $employee->id,
                    'name' => $employee->name,
                    'date' => $birthdayThisYear->locale('id')->isoFormat('D MMMM YYYY'),
                    'days_until' => $daysUntil,
                    'birthday_date' => $birthdayThisYear,
                ];
            })
            ->filter(function ($item) {
                // Only show birthdays within next 30 days
                return $item['days_until'] >= 0 && $item['days_until'] <= 30;
            })
            ->sortBy('days_until')
            ->take($limit)
            ->values()
            ->map(function ($item) {
                // Remove helper fields before returning
                unset($item['days_until']);
                unset($item['birthday_date']);
                return $item;
            })
            ->toArray();

        return $upcomingBirthdays;
    }
}
