<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Schedule daily checklist reminders at 01:00 WITA
// This command reads database and schedules reminder jobs for the day
Schedule::command('checklist:schedule-daily-reminders')
    ->dailyAt('01:00')
    ->timezone('Asia/Makassar')
    ->description('Schedule today\'s checklist reminders based on database settings');

// Schedule employee balance update to run yearly on January 1st
Schedule::command('app:update-balance-employee')
    ->yearlyOn(1, 1, '00:00') // January 1st at midnight
    ->timezone('Asia/Makassar')
    ->description('Update employee leave balances and receivable balances for the new year');
