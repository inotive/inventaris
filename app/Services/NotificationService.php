<?php

namespace App\Services;

use App\Jobs\SendOneSignalPush;
use App\Models\LogActivity;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class NotificationService
{
    /**
     * Send notification to admin/SPV/HRD when receiving submissions
     */
    public function notifyAdminsOnSubmission($submissionType, $submissionId, $submittedBy, $details = [])
    {
        try {
            // Get admin, supervisor, and HRD users
            $adminUsers = User::role(['Admin', 'Superadmin', 'SPV', 'HRD'])
                ->whereNotNull('external_id')
                ->get();

            // Log the admin users found
            Log::info('[NotificationService] Admin users retrieved for notification', [
                'submission_type' => $submissionType,
                'admin_count' => $adminUsers->count(),
                'admin_ids' => $adminUsers->pluck('id')->toArray(),
                'admin_external_ids' => $adminUsers->pluck('external_id')->toArray(),
            ]);

            $title = $this->getSubmissionTitle($submissionType);
            $message = $this->getSubmissionMessage($submissionType, $submittedBy, $details);
            $category = $this->getCategoryForSubmission($submissionType);

            $recipientIds = [];
            foreach ($adminUsers as $user) {
                SendOneSignalPush::dispatch(
                    $title,
                    $message,
                    $user->external_id,
                    $submittedBy->id ?? null,
                    $this->getModelClass($submissionType),
                    $submissionId,
                    $category,
                    0,
                    1
                );
                $recipientIds[] = $user->external_id;
            }

            // Log info
            Log::info('[NotificationService] Submission notification sent', [
                'submission_type' => $submissionType,
                'submission_id' => $submissionId,
                'submitted_by' => $submittedBy->name ?? 'Unknown',
                'submitted_by_id' => $submittedBy->id ?? null,
                'recipients_count' => count($recipientIds),
                'recipients' => $recipientIds,
                'title' => $title,
                'message' => $message,
                'category' => $category,
            ]);

            // // Log activity
            // LogActivity::create([
            //     'users_id' => $submittedBy->id ?? null,
            //     'activity' => 'notification_sent',
            //     'description' => "Notifikasi pengajuan {$submissionType} dikirim ke " . count($recipientIds) . " admin/supervisor",
            //     'data' => json_encode([
            //         'type' => 'submission_notification',
            //         'submission_type' => $submissionType,
            //         'submission_id' => $submissionId,
            //         'title' => $title,
            //         'message' => $message,
            //         'recipients' => $recipientIds,
            //         'details' => $details,
            //     ]),
            // ]);
        } catch (\Throwable $e) {
            // Log error
            Log::error('[NotificationService] Failed to send submission notification', [
                'submission_type' => $submissionType,
                'submission_id' => $submissionId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            // Silent fail - notification should not block API
            report($e);
        }
    }

    /**
     * Send notification to staff when receiving feedback on submissions
     */
    public function notifyStaffOnFeedback($submissionType, $submissionId, $staffUserId, $feedbackStatus, $details = [])
    {
        try {
            $staff = User::find($staffUserId);
            if (!$staff || !$staff->external_id) {
                return;
            }

            $title = $this->getFeedbackTitle($submissionType, $feedbackStatus);
            $message = $this->getFeedbackMessage($submissionType, $feedbackStatus, $details);
            $category = $this->getCategoryForSubmission($submissionType);

            SendOneSignalPush::dispatch(
                $title,
                $message,
                $staff->external_id,
                $staffUserId,
                $this->getModelClass($submissionType),
                $submissionId,
                $category,
                0,
                1
            );

            // Log info
            Log::info('[NotificationService] Feedback notification sent', [
                'submission_type' => $submissionType,
                'submission_id' => $submissionId,
                'feedback_status' => $feedbackStatus,
                'recipient' => $staff->name,
                'recipient_id' => $staffUserId,
                'title' => $title,
                'message' => $message,
                'category' => $category,
                'approved_by' => Auth::user()->name ?? 'System',
            ]);

            // Log activity
            LogActivity::create([
                'users_id' => Auth::id() ?? null,
                'model_type' => $this->getModelClass($submissionType),
                'model_id' => $submissionId,
                'description' => "Notifikasi feedback {$feedbackStatus} untuk pengajuan {$submissionType} dikirim ke {$staff->name}",
            ]);
        } catch (\Throwable $e) {
            // Log error
            Log::error('[NotificationService] Failed to send feedback notification', [
                'submission_type' => $submissionType,
                'submission_id' => $submissionId,
                'staff_user_id' => $staffUserId,
                'feedback_status' => $feedbackStatus,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            // Silent fail - notification should not block API
            report($e);
        }
    }

    /**
     * Send notification to all staff during check-in/check-out
     */
    public function notifyAllStaffOnAttendance($employeeName, $attendanceType, $time)
    {
        try {
            // Get all users with subscription IDs
            $allUsers = User::whereNotNull('external_id')->get();

            $title = $attendanceType === 'masuk' ? 'Absensi Masuk' : 'Absensi Keluar';
            $message = "{$employeeName} melakukan {$attendanceType} pada {$time}";
            $category = 'attendance';

            $recipientIds = [];
            foreach ($allUsers as $user) {
                SendOneSignalPush::dispatch(
                    $title,
                    $message,
                    $user->external_id,
                    null,
                    'App\\Models\\Attendance',
                    null,
                    $category,
                    0,
                    1
                );
                $recipientIds[] = $user->id;
            }

            // Log info
            Log::info('[NotificationService] Attendance notification sent', [
                'attendance_type' => $attendanceType,
                'employee_name' => $employeeName,
                'time' => $time,
                'recipients_count' => count($recipientIds),
                'title' => $title,
                'message' => $message,
                'category' => $category,
            ]);

            // Log activity
            LogActivity::create([
                'users_id' => null,
                'model_type' => 'App\\Models\\Attendance',
                'model_id' => null,
                'description' => "Notifikasi absensi {$attendanceType} dari {$employeeName} dikirim ke " . count($recipientIds) . " pengguna",
            ]);
        } catch (\Throwable $e) {
            // Log error
            Log::error('[NotificationService] Failed to send attendance notification', [
                'employee_name' => $employeeName,
                'attendance_type' => $attendanceType,
                'time' => $time,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            // Silent fail - notification should not block API
            report($e);
        }
    }

    /**
     * Send notification to SPV when staff sends checklist
     */
    public function notifySupervisorsOnChecklist($checklistName, $submittedBy, $departmentId, $inspectionId)
    {
        try {
            // Get supervisors in the same department
            $supervisors = User::role('Supervisor')
                ->whereHas('employee', function ($query) use ($departmentId) {
                    $query->where('department_id', $departmentId);
                })
                ->whereNotNull('external_id')
                ->get();

            $title = 'Checklist Diselesaikan';
            $message = "{$submittedBy} menyelesaikan checklist {$checklistName}";
            $category = 'checklist';

            $recipientIds = [];
            foreach ($supervisors as $supervisor) {
                SendOneSignalPush::dispatch(
                    $title,
                    $message,
                    $supervisor->external_id,
                    $submittedBy->id ?? null,
                    'App\\Models\\Inspection',
                    $inspectionId,
                    $category,
                    0,
                    1
                );
                $recipientIds[] = $supervisor->id;
            }

            // Log info
            Log::info('[NotificationService] Checklist notification sent', [
                'checklist_name' => $checklistName,
                'inspection_id' => $inspectionId,
                'department_id' => $departmentId,
                'submitted_by' => $submittedBy,
                'recipients_count' => count($recipientIds),
                'recipients' => $recipientIds,
                'title' => $title,
                'message' => $message,
                'category' => $category,
            ]);
        } catch (\Throwable $e) {
            // Log error
            Log::error('[NotificationService] Failed to send checklist notification', [
                'checklist_name' => $checklistName,
                'inspection_id' => $inspectionId,
                'department_id' => $departmentId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            // Silent fail - notification should not block API
            report($e);
        }
    }

    /**
     * Send checklist reminder notification to a specific staff member
     */
    public function notifyStaffOnChecklistReminder($externalId, $title, $message, $additionalData = [])
    {
        try {
            if (!$externalId) {
                Log::warning('[NotificationService] Checklist reminder skipped - no external_id', [
                    'title' => $title,
                    'message' => $message,
                ]);
                return;
            }

            // Find user by external_id to get user_id
            $user = \App\Models\User::where('external_id', $externalId)->first();

            if (!$user) {
                Log::warning('[NotificationService] Checklist reminder skipped - user not found for external_id', [
                    'external_id' => $externalId,
                    'title' => $title,
                    'message' => $message,
                ]);
                return;
            }

            $category = 'checklist_reminder';
            $pendingCount = $additionalData['pending_count'] ?? 0;
            $checklistIds = $additionalData['checklist_ids'] ?? [];
            $checklistNames = $additionalData['checklists'] ?? [];
            $date = $additionalData['date'] ?? now()->format('Y-m-d');

            // Build rich additional_data for notification
            $enrichedAdditionalData = [
                'pending_count' => $pendingCount,
                'checklists' => $checklistNames,
                'checklist_ids' => $checklistIds,
                'date' => $date,
            ];

            // Log before dispatching job
            Log::info('[NotificationService] Dispatching SendOneSignalPush for checklist reminder', [
                'external_id' => $externalId,
                'user_id' => $user->id,
                'user_name' => $user->name ?? 'unknown',
                'title' => $title,
                'pending_count' => $pendingCount,
                'checklist_ids' => $checklistIds,
                'additional_data' => $enrichedAdditionalData,
            ]);

            // Dispatch job with additional_data - OneSignalService will handle notification creation
            SendOneSignalPush::dispatch(
                $title,
                $message,
                $externalId,
                $user->id,
                'App\\Models\\Checklist',
                !empty($checklistIds) ? $checklistIds[0] : null,
                $category,
                0,
                1,
                $enrichedAdditionalData // Pass additional_data to job
            );

            // Log info
            Log::info('[NotificationService] Checklist reminder notification dispatched', [
                'external_id' => $externalId,
                'user_id' => $user->id,
                'title' => $title,
                'message' => $message,
                'pending_count' => $pendingCount,
                'category' => $category,
            ]);
        } catch (\Throwable $e) {
            // Log error
            Log::error('[NotificationService] Failed to send checklist reminder notification', [
                'external_id' => $externalId,
                'title' => $title,
                'message' => $message,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            // Re-throw to let caller handle it
            throw $e;
        }
    }

    /**
     * Send notification to supervisors/admins when inspection is submitted
     *
     * @param int $inspectionId
     * @param object $submittedBy User who submitted the inspection
     * @param array $details Additional details (checklist_name, inspection_number, model_type, etc.)
     */
    public function notifySupervisorsOnInspectionSubmit($inspectionId, $submittedBy, $details = [])
    {
        try {
            // Get Superadmin, Admin, and Supervisor users
            $supervisorUsers = User::role(['Superadmin', 'Admin', 'Supervisor'])
                ->whereNotNull('external_id')
                ->where('id', '!=', $submittedBy->id) // Don't notify the submitter
                ->get();

            Log::info('[NotificationService] Supervisor users retrieved for inspection notification', [
                'inspection_id' => $inspectionId,
                'supervisor_count' => $supervisorUsers->count(),
                'supervisor_ids' => $supervisorUsers->pluck('id')->toArray(),
                'supervisor_names' => $supervisorUsers->pluck('name')->toArray(),
            ]);

            $checklistName = $details['checklist_name'] ?? 'Checklist';
            $inspectionNumber = $details['inspection_number'] ?? '';
            $modelType = $details['model_type'] ?? null;
            $modelName = $details['model_name'] ?? '';

            $title = 'Checklist Diselesaikan';
            $employeeName = $submittedBy->name ?? $submittedBy->employee->name ?? 'Karyawan';

            // Build message based on model type
            if ($modelType === 'App\\Models\\Vehicle' && $modelName) {
                $message = "{$employeeName} menyelesaikan checklist {$checklistName} untuk kendaraan {$modelName}";
            } elseif ($modelType === 'App\\Models\\Employee' && $modelName) {
                $message = "{$employeeName} menyelesaikan checklist {$checklistName} untuk karyawan {$modelName}";
            } else {
                $message = "{$employeeName} menyelesaikan checklist {$checklistName}";
            }

            $category = 'inspection_submitted';

            // Build additional_data for rich notification content
            $additionalData = [
                'inspection_id' => $inspectionId,
                'inspection_number' => $inspectionNumber,
                'checklist_name' => $checklistName,
                'inspected_model_type' => $modelType,  // Renamed to avoid conflict with notification model_type
                'model_name' => $modelName,
                'submitted_by' => $employeeName,
                'submitted_by_id' => $submittedBy->id,
            ];

            $recipientIds = [];
            $notificationIds = [];
            foreach ($supervisorUsers as $user) {
                // Dispatch job with additional_data - OneSignalService will handle notification creation
                SendOneSignalPush::dispatch(
                    $title,
                    $message,
                    $user->external_id,
                    $user->id,
                    'App\\Models\\Inspection',
                    $inspectionId,
                    $category,
                    0,
                    1,
                    $additionalData // Pass additional_data to job
                );
                $recipientIds[] = $user->external_id;
            }

            Log::info('[NotificationService] Inspection submission notification dispatched to supervisors', [
                'inspection_id' => $inspectionId,
                'inspection_number' => $inspectionNumber,
                'checklist_name' => $checklistName,
                'submitted_by' => $employeeName,
                'submitted_by_id' => $submittedBy->id,
                'recipients_count' => count($recipientIds),
                'title' => $title,
                'message' => $message,
                'category' => $category,
                'additional_data' => $additionalData,
            ]);

            // Log activity
            LogActivity::create([
                'users_id' => $submittedBy->id,
                'model_type' => 'App\\Models\\Inspection',
                'model_id' => $inspectionId,
                'description' => "Notifikasi checklist {$checklistName} dikirim ke " . count($recipientIds) . " supervisor/admin",
            ]);
        } catch (\Throwable $e) {
            Log::error('[NotificationService] Failed to send inspection submission notification', [
                'inspection_id' => $inspectionId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            // Silent fail - notification should not block API
            report($e);
        }
    }

    /**
     * Get submission title based on type
     */
    private function getSubmissionTitle($submissionType)
    {
        $titles = [
            'sick' => 'Pengajuan Sakit',
            'leave' => 'Pengajuan Cuti',
            'overtime' => 'Pengajuan Lembur',
            'procurement' => 'Pengajuan Pengadaan',
            'loan' => 'Pengajuan Piutang',
            'usage' => 'Pengajuan Penggunaan',
            'daily_report' => 'Laporan Harian',
            'employee' => 'Pengajuan Karyawan',
        ];

        return $titles[$submissionType] ?? 'Pengajuan Baru';
    }

    /**
     * Get submission message based on type
     */
    private function getSubmissionMessage($submissionType, $submittedBy, $details = [])
    {
        $employeeName = $submittedBy->name ?? 'Karyawan';

        switch ($submissionType) {
            case 'sick':
                return "{$employeeName} mengajukan sakit";
            case 'leave':
                $startDate = $details['start_date'] ?? '';
                $endDate = $details['end_date'] ?? '';
                return "{$employeeName} mengajukan cuti {$startDate} - {$endDate}";
            case 'overtime':
                $date = $details['date'] ?? '';
                return "{$employeeName} mengajukan lembur pada {$date}";
            case 'procurement':
                return "{$employeeName} mengajukan permintaan pengadaan";
            case 'loan':
                $amount = $details['amount'] ?? '';
                return "{$employeeName} mengajukan piutang sebesar {$amount}";
            case 'usage':
                return "{$employeeName} mengajukan penggunaan barang";
            case 'daily_report':
                return "{$employeeName} mengirim laporan harian";
            case 'employee':
                return "{$employeeName} mengajukan data karyawan";
            default:
                return "{$employeeName} mengirim pengajuan baru";
        }
    }

    /**
     * Get feedback title based on type and status
     */
    private function getFeedbackTitle($submissionType, $feedbackStatus)
    {
        $statusText = $feedbackStatus === 'approved' ? 'Disetujui' : 'Ditolak';
        $typeText = $this->getSubmissionTitle($submissionType);

        return "{$typeText} {$statusText}";
    }

    /**
     * Get feedback message based on type and status
     */
    private function getFeedbackMessage($submissionType, $feedbackStatus, $details = [])
    {
        $statusText = $feedbackStatus === 'approved' ? 'disetujui' : 'ditolak';
        $typeText = strtolower($this->getSubmissionTitle($submissionType));
        $note = $details['note'] ?? '';

        $message = "Pengajuan {$typeText} Anda telah {$statusText}";
        if ($note) {
            $message .= ". Catatan: {$note}";
        }

        return $message;
    }

    /**
     * Get model class based on submission type
     */
    private function getModelClass($submissionType)
    {
        $models = [
            'sick' => 'App\\Models\\EmployeeLeaveRequest',
            'leave' => 'App\\Models\\EmployeeLeaveRequest',
            'overtime' => 'App\\Models\\EmployeeOvertime',
            'procurement' => 'App\\Models\\MaterialRequest',
            'loan' => 'App\\Models\\Receivable',
            'usage' => 'App\\Models\\GoodIssue',
            'daily_report' => 'App\\Models\\Submission',
            'employee' => 'App\\Models\\Submission',
            'checklist' => 'App\\Models\\Checklist',
        ];

        return $models[$submissionType] ?? 'App\\Models\\Submission';
    }

    /**
     * Get category based on submission type for mobile app deep linking
     */
    private function getCategoryForSubmission($submissionType)
    {
        $categories = [
            'sick' => 'sick_leave',
            'leave' => 'annual_leave',
            'overtime' => 'overtime',
            'procurement' => 'procurement',
            'loan' => 'loan',
            'usage' => 'usage',
            'daily_report' => 'daily_report',
            'employee' => 'employee_submission',
        ];

        return $categories[$submissionType] ?? 'general';
    }
}
