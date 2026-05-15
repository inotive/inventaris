<?php

namespace App\Http\Controllers\Api\v1;

use App\Helpers\ResponseFormatter;
use App\Http\Controllers\Controller;
use App\Models\CheklistEmployee;
use App\Models\Inspection;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ChecklistReminderController extends Controller
{
    /**
     * Get pending checklists count for authenticated user
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getPendingCount(Request $request)
    {
        try {
            $user = Auth::user();

            if (!$user->employee) {
                return ResponseFormatter::error(null, 'Employee not found', 404);
            }

            $today = Carbon::today();
            $employeeId = $user->employee->id;

            // Get all assigned checklists for this employee
            $assignments = CheklistEmployee::with('checklist')
                ->where('employee_id', $employeeId)
                ->get();

            $pendingChecklists = [];
            $pendingCount = 0;

            foreach ($assignments as $assignment) {
                $checklist = $assignment->checklist;

                // Check if checklist has been completed today
                $completedToday = Inspection::where('checklist_id', $checklist->id)
                    ->whereDate('submit_date', $today)
                    ->where(function ($q) use ($user) {
                        $q->where('submitted_by', $user->id)
                          ->orWhere('created_by', $user->id);
                    })
                    ->exists();

                if (!$completedToday) {
                    $pendingCount++;
                    $pendingChecklists[] = [
                        'id' => $checklist->id,
                        'name' => $checklist->name,
                        'category' => $checklist->category ? $checklist->category->name : null,
                        'type' => $checklist->type,
                    ];
                }
            }

            return ResponseFormatter::success([
                'pending_count' => $pendingCount,
                'checklists' => $pendingChecklists,
                'date' => $today->format('Y-m-d'),
                'message' => $pendingCount > 0
                    ? "Kamu memiliki tugas mengisi checklist sebanyak {$pendingCount} hari ini"
                    : "Semua checklist hari ini sudah terisi",
            ], 'Pending checklists retrieved successfully');

        } catch (\Exception $e) {
            return ResponseFormatter::error(null, 'Failed to get pending checklists: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Get pending checklists for a specific date
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getPendingByDate(Request $request)
    {
        try {
            $user = Auth::user();

            if (!$user->employee) {
                return ResponseFormatter::error(null, 'Employee not found', 404);
            }

            $date = $request->input('date', Carbon::today()->format('Y-m-d'));
            $targetDate = Carbon::parse($date);
            $employeeId = $user->employee->id;

            // Get all assigned checklists for this employee
            $assignments = CheklistEmployee::with('checklist')
                ->where('employee_id', $employeeId)
                ->get();

            $pendingChecklists = [];
            $completedChecklists = [];

            foreach ($assignments as $assignment) {
                $checklist = $assignment->checklist;

                // Check if checklist has been completed on target date
                $inspection = Inspection::where('checklist_id', $checklist->id)
                    ->whereDate('submit_date', $targetDate)
                    ->where(function ($q) use ($user) {
                        $q->where('submitted_by', $user->id)
                          ->orWhere('created_by', $user->id);
                    })
                    ->first();

                $checklistData = [
                    'id' => $checklist->id,
                    'name' => $checklist->name,
                    'category' => $checklist->category ? $checklist->category->name : null,
                    'type' => $checklist->type,
                    'status' => $inspection ? 'completed' : 'pending',
                    'completed_at' => $inspection ? $inspection->submit_date->format('Y-m-d H:i:s') : null,
                ];

                if ($inspection) {
                    $completedChecklists[] = $checklistData;
                } else {
                    $pendingChecklists[] = $checklistData;
                }
            }

            return ResponseFormatter::success([
                'date' => $targetDate->format('Y-m-d'),
                'pending_count' => count($pendingChecklists),
                'completed_count' => count($completedChecklists),
                'pending_checklists' => $pendingChecklists,
                'completed_checklists' => $completedChecklists,
            ], 'Checklists retrieved successfully');

        } catch (\Exception $e) {
            return ResponseFormatter::error(null, 'Failed to get checklists: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Mark notification as read
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function markAsRead(Request $request)
    {
        try {
            $notificationId = $request->input('notification_id');

            $notification = \App\Models\Notification::where('id', $notificationId)
                ->where('user_id', Auth::id())
                ->first();

            if (!$notification) {
                return ResponseFormatter::error(null, 'Notification not found', 404);
            }

            $notification->update(['status' => 1]); // 1 = terbaca

            return ResponseFormatter::success($notification, 'Notification marked as read');

        } catch (\Exception $e) {
            return ResponseFormatter::error(null, 'Failed to mark notification as read: ' . $e->getMessage(), 500);
        }
    }
}
