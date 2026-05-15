<?php

namespace App\Console\Commands;

use App\Models\CheklistEmployee;
use App\Models\Employee;
use App\Models\Inspection;
use App\Models\Notification;
use App\Services\NotificationService;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class SendChecklistReminder extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'checklist:send-reminder';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send reminder notifications to employees who have pending checklists';

    protected $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        parent::__construct();
        $this->notificationService = $notificationService;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting checklist reminder process...');

        $now = now('Asia/Makassar');
        $today = Carbon::today('Asia/Makassar');
        $currentTime = $now->format('H:i');
        $currentDayOfWeek = $now->dayOfWeek; // 0 = Sunday, 1 = Monday, ..., 6 = Saturday

        // Get all employees with assigned checklists
        $employeesWithChecklists = CheklistEmployee::with(['employee.user', 'checklist'])
            ->get()
            ->groupBy('employee_id');

        $totalSent = 0;

        foreach ($employeesWithChecklists as $employeeId => $assignments) {
            $employee = $assignments->first()->employee;

            if (!$employee || !$employee->user) {
                continue;
            }

            // Count pending checklists that need a reminder based on durasi and count
            $pendingCount = 0;
            $checklistNames = [];
            $checklistIds = [];

            foreach ($assignments as $assignment) {
                $checklist = $assignment->checklist;

                if (!$checklist) {
                    continue;
                }

                // Skip if reminder is disabled for this checklist
                if (!$checklist->reminder_enabled) {
                    continue;
                }

                // Check if it's time to send reminder based on reminder_time
                // Parse reminder_time in Asia/Makassar timezone explicitly
                $reminderTimeStr = $checklist->reminder_time ?? '15:20:00';
                $reminderTime = Carbon::today('Asia/Makassar')->setTimeFromTimeString($reminderTimeStr);
                $timeDiff = abs($now->diffInMinutes($reminderTime));

                // Skip if not within 5 minutes of reminder time
                if ($timeDiff > 5) {
                    continue;
                }

                // Check frequency
                if ($checklist->reminder_frequency === 'weekly') {
                    $reminderDays = is_array($checklist->reminder_days) ? $checklist->reminder_days : [];
                    if (!in_array($currentDayOfWeek, $reminderDays)) {
                        $this->info("Skipping checklist {$checklist->name} - not scheduled for " . $now->format('l'));
                        continue;
                    }
                }

                if ($checklist->reminder_frequency === 'monthly') {
                    // Only send on the 1st of each month
                    if ($now->day != 1) {
                        continue;
                    }
                }

                // Check if checklist has been completed TODAY
                $completedToday = Inspection::where('checklist_id', $checklist->id)
                    ->whereDate('submit_date', $today)
                    ->where(function ($q) use ($employee) {
                        $q->where('submitted_by', $employee->user_id)
                            ->orWhere('created_by', $employee->user_id);
                    })
                    ->exists();

                // If not completed today, add to pending list
                if (!$completedToday) {
                    $pendingCount++;
                    $checklistNames[] = $checklist->name;
                    $checklistIds[] = $checklist->id;
                }
            }

            // Send notification if there are pending checklists that qualify for a reminder
            if ($pendingCount > 0) {
                $message = "Kamu memiliki tugas mengisi checklist sebanyak {$pendingCount} yang belum selesai";

                // Send push notification via OneSignal (will also create in-app notification)
                try {
                    if ($employee->user->external_id) {
                        $this->notificationService->notifyStaffOnChecklistReminder(
                            $employee->user->external_id,
                            'Reminder Checklist',
                            $message,
                            [
                                'type' => 'checklist_reminder',
                                'pending_count' => $pendingCount,
                                'checklist_ids' => $checklistIds,
                                'checklists' => $checklistNames,
                                'date' => $today->format('Y-m-d'),
                            ]
                        );
                    }
                } catch (\Exception $e) {
                    $this->warn("Failed to send push notification to {$employee->name}: " . $e->getMessage());
                }

                $totalSent++;
                $this->info("✓ Sent reminder to {$employee->name} ({$pendingCount} checklists)");
            }
        }

        $this->info("Checklist reminder process completed. Total reminders sent: {$totalSent}");

        return Command::SUCCESS;
    }
}
