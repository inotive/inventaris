<?php

namespace App\Jobs;

use App\Models\CheklistEmployee;
use App\Models\Checklist;
use App\Models\Inspection;
use App\Services\NotificationService;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendChecklistReminderJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $checklistId;

    /**
     * Create a new job instance.
     */
    public function __construct(int $checklistId)
    {
        $this->checklistId = $checklistId;
    }

    /**
     * Execute the job.
     */
    public function handle(NotificationService $notificationService): void
    {
        $today = Carbon::today('Asia/Makassar');
        $checklist = Checklist::find($this->checklistId);

        if (!$checklist || !$checklist->reminder_enabled) {
            Log::warning('[SendChecklistReminderJob] Checklist not found or reminder disabled', [
                'checklist_id' => $this->checklistId,
            ]);
            return;
        }

        Log::info('[SendChecklistReminderJob] Processing checklist reminder job', [
            'checklist_id' => $checklist->id,
            'checklist_name' => $checklist->name,
            'reminder_time' => $checklist->reminder_time,
        ]);

        // Get all employees assigned to this checklist
        $assignments = CheklistEmployee::where('checklist_id', $checklist->id)
            ->with(['employee.user'])
            ->get();

        $totalSent = 0;

        foreach ($assignments as $assignment) {
            $employee = $assignment->employee;

            if (!$employee || !$employee->user || !$employee->user->external_id) {
                continue;
            }

            // Check if this employee already completed this checklist today
            $completedToday = Inspection::where('checklist_id', $checklist->id)
                ->whereDate('submit_date', $today)
                ->where(function ($q) use ($employee) {
                    $q->where('submitted_by', $employee->user_id)
                        ->orWhere('created_by', $employee->user_id);
                })
                ->exists();

            // Skip if already completed
            if ($completedToday) {
                continue;
            }

            // Send reminder to this employee
            $message = "Kamu memiliki tugas mengisi checklist: {$checklist->name}";

            try {
                $notificationService->notifyStaffOnChecklistReminder(
                    $employee->user->external_id,
                    'Reminder Checklist',
                    $message,
                    [
                        'type' => 'checklist_reminder',
                        'pending_count' => 1,
                        'checklist_ids' => [$checklist->id],
                        'checklists' => [$checklist->name],
                        'date' => $today->format('Y-m-d'),
                    ]
                );
                $totalSent++;
            } catch (\Exception $e) {
                Log::warning("[SendChecklistReminderJob] Failed to send reminder to {$employee->name}", [
                    'error' => $e->getMessage(),
                ]);
            }
        }

        Log::info('[SendChecklistReminderJob] Checklist reminder job completed', [
            'checklist_id' => $checklist->id,
            'checklist_name' => $checklist->name,
            'total_sent' => $totalSent,
        ]);
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error('[SendChecklistReminderJob] Checklist reminder job failed', [
            'checklist_id' => $this->checklistId,
            'error' => $exception->getMessage(),
            'trace' => $exception->getTraceAsString(),
        ]);
    }
}
