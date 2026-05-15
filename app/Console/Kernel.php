<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     *
     * The schedule method is where you can define all of your scheduled tasks.
     * Each task is wrapped in a simple, expressive API that makes it easy to define recurring tasks.
     */
    protected function schedule(Schedule $schedule): void
    {
        // Run checklist reminder every minute
        // Command akan check database untuk menentukan apakah reminder perlu dikirim
        // berdasarkan reminder_time, reminder_frequency, reminder_days, durasi, dan count
        $schedule->command('checklist:send-reminder')
            ->everyMinute()
            ->withoutOverlapping()
            ->onFailure(function () {
                \Illuminate\Support\Facades\Log::error('Checklist reminder command failed');
            })
            ->onSuccess(function () {
                \Illuminate\Support\Facades\Log::info('Checklist reminder command executed successfully');
            });
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
