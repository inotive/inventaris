<?php

namespace App\Console\Commands;

use App\Jobs\SendChecklistReminderJob;
use App\Models\Checklist;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class ScheduleDailyChecklistReminders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'checklist:schedule-daily-reminders';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Schedule checklist reminders for today based on database settings';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Scheduling daily checklist reminders...');
        $this->line('');

        $now = now('Asia/Makassar');
        $today = Carbon::today('Asia/Makassar');
        $currentDayOfWeek = $now->dayOfWeek; // 0 = Sunday, 1 = Monday, ..., 6 = Saturday

        // Get all active checklists with reminder enabled
        $checklists = Checklist::where('reminder_enabled', true)
            ->where('status', 'Active')
            ->get();

        $scheduled = 0;
        $skipped = 0;
        $scheduledList = [];
        $skippedList = [];

        foreach ($checklists as $checklist) {
            // Check frequency
            $shouldSchedule = false;
            $skipReason = '';

            if ($checklist->reminder_frequency === 'daily') {
                $shouldSchedule = true;
            } elseif ($checklist->reminder_frequency === 'weekly') {
                $reminderDays = is_array($checklist->reminder_days)
                    ? $checklist->reminder_days
                    : json_decode($checklist->reminder_days, true) ?? [];

                if (in_array($currentDayOfWeek, $reminderDays)) {
                    $shouldSchedule = true;
                } else {
                    $skipReason = 'not scheduled for today (weekly: ' . implode(',', $reminderDays) . ')';
                }
            } elseif ($checklist->reminder_frequency === 'monthly') {
                // Only on the 1st of the month
                if ($now->day === 1) {
                    $shouldSchedule = true;
                } else {
                    $skipReason = 'not scheduled for today (monthly: day 1 only)';
                }
            }

            if (!$shouldSchedule) {
                $skippedList[] = [
                    'name' => $checklist->name,
                    'reason' => $skipReason,
                    'frequency' => $checklist->reminder_frequency,
                    'time' => $checklist->reminder_time,
                ];
                $skipped++;
                continue;
            }

            // Parse reminder time
            $reminderTimeStr = $checklist->reminder_time ?? '15:20:00';
            try {
            $reminderTime = Carbon::today('Asia/Makassar')->setTimeFromTimeString($reminderTimeStr);
                Log::info("Parsing reminder time for checklist: {$checklist->name}", [
                    'checklist_id' => $checklist->id,
                    'reminder_time_string' => $reminderTimeStr,
                    'parsed_reminder_time' => $reminderTime->toDateTimeString(),
                    'current_time' => $now->toDateTimeString(),
                ]);
            } catch (\Exception $e) {
                Log::error("Failed to parse reminder time for checklist: {$checklist->name}", [
                    'checklist_id' => $checklist->id,
                    'reminder_time_string' => $reminderTimeStr,
                    'error' => $e->getMessage(),
                ]);
                $skippedList[] = [
                    'name' => $checklist->name,
                    'reason' => "invalid reminder time format ({$reminderTimeStr})",
                    'frequency' => $checklist->reminder_frequency,
                    'time' => $checklist->reminder_time,
                ];
                $skipped++;
                continue;
            }

            // Skip if reminder time already passed
            if ($reminderTime->isPast()) {
                $skippedList[] = [
                    'name' => $checklist->name,
                    'reason' => "reminder time already passed ({$reminderTimeStr})",
                    'frequency' => $checklist->reminder_frequency,
                    'time' => $checklist->reminder_time,
                ];
                $skipped++;
                continue;
            }

            // Calculate delay in seconds from now until reminder time
            $delayInSeconds = $now->diffInSeconds($reminderTime, false);

            // Dispatch job with delay (using Carbon instance is more reliable)
            SendChecklistReminderJob::dispatch($checklist->id)
                ->delay($reminderTime);

            $delayMinutes = round($delayInSeconds / 60, 1);
            $scheduledList[] = [
                'name' => $checklist->name,
                'time' => $reminderTimeStr,
                'delay_minutes' => $delayMinutes,
                'frequency' => $checklist->reminder_frequency,
            ];
            $scheduled++;
        }

        // Display scheduled checklists
        if (!empty($scheduledList)) {
            $this->info('✅ SCHEDULED CHECKLISTS:');
            $this->table(
                ['Checklist Name', 'Time', 'Delay (min)', 'Frequency'],
                array_map(function ($item) {
                    return [
                        $item['name'],
                        $item['time'],
                        $item['delay_minutes'],
                        $item['frequency'],
                    ];
                }, $scheduledList)
            );
            $this->line('');
        }

        // Display skipped checklists
        if (!empty($skippedList)) {
            $this->warn('⏭️  SKIPPED CHECKLISTS:');
            $this->table(
                ['Checklist Name', 'Reason', 'Frequency', 'Time'],
                array_map(function ($item) {
                    return [
                        $item['name'],
                        $item['reason'],
                        $item['frequency'],
                        $item['time'],
                    ];
                }, $skippedList)
            );
            $this->line('');
        }

        // Log to Laravel log
        Log::info('[ScheduleDailyChecklistReminders] Daily checklist reminders scheduled', [
            'scheduled' => $scheduled,
            'skipped' => $skipped,
            'total' => $checklists->count(),
            'scheduled_list' => array_column($scheduledList, 'name'),
            'skipped_list' => array_map(function ($item) {
                return $item['name'] . ' (' . $item['reason'] . ')';
            }, $skippedList),
        ]);

        // Summary
        $this->info('═══════════════════════════════════════════════════════');
        $this->info('SUMMARY:');
        $this->info("  ✅ Scheduled: {$scheduled}");
        $this->info("  ⏭️  Skipped: {$skipped}");
        $this->info("  📋 Total: {$checklists->count()}");
        $this->info('═══════════════════════════════════════════════════════');

        return Command::SUCCESS;
    }
}
