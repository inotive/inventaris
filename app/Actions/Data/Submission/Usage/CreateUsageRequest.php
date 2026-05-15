<?php

namespace App\Actions\Data\Submission\Usage;

use App\Enums\SubmissionStatusEnum;
use App\Models\GoodIssue;
use App\Models\MaterialRequest;
use App\Models\Submission;
use App\Services\NotificationService;
use Illuminate\Support\Facades\DB;

class CreateUsageRequest
{
    /**
     * Get submission statistics based on type and status.
     *
     * @param string|null $type
     * @return void
     */
    public function execute($user, $data)
    {
        DB::transaction(function () use ($user, $data) {

            $goodIssue = GoodIssue::create([
                'department_id' => $user->employee->department_id,
                'request_by' => $user->employee ? $user->employee->id : null,
                'date' => $data['date'],
                'requirement' => $data['note'] ?? null,
                'status' => 'pending'
            ]);

            foreach ($data['items'] as $item) {
                $goodIssue->items()->create([
                    'item_id' => $item['id'],
                    'quantity_issued' => $item['quantity'],
                    'note' => $item['note'] ?? null,
                ]);
            }

            // Send push notification to admin/SPV
            $notificationService = app(NotificationService::class);
            $notificationService->notifyAdminsOnSubmission(
                'usage',
                $goodIssue->id,
                $user,
                [
                    'date' => $data['date'],
                    'item_count' => count($data['items'])
                ]
            );
        });
    }
}
