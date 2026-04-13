<?php

namespace App\Actions\Data\Submission\Procurement;

use App\Models\MaterialRequest;
use App\Models\MaterialRequestItem;
use App\Services\NotificationService;
use Illuminate\Support\Facades\DB;

class CreateProcurementRequest
{
    /**
     * Create procurement request
     *
     * @param mixed $user
     * @param array $data
     * @return void
     */
    public function execute($user, $data)
    {
        DB::transaction(function () use ($user, $data) {

            $materialRequest = MaterialRequest::create([
                'department_id' => $data['department_id'],
                'requested_by' => $user->employee ? $user->employee->id : null,
                'requested_at' => $data['requested_at'],
                'requirement' => $data['requirement'],
                'note' => $data['note'] ?? null,
                'status' => 'pending'
            ]);

            $materialRequestId = $materialRequest->id;

            foreach ($data['items'] as $item) {
                MaterialRequestItem::create([
                    'material_request_id' => $materialRequestId,
                    'item_id' => $item['item_id'],
                    'quantity_requested' => $item['quantity'],
                    'note' => $item['note'] ?? null,
                ]);
            }

            // Send push notification to admin/SPV using NotificationService
            $notificationService = app(NotificationService::class);
            $notificationService->notifyAdminsOnSubmission(
                'procurement',
                $materialRequestId,
                $user,
                [
                    'requirement' => $data['requirement'],
                    'item_count' => count($data['items'])
                ]
            );

            return $materialRequest;
        });

    }
}
