<?php

namespace App\Actions\Data\Submission\General;

use App\Models\GeneralSubmission;
use App\Services\NotificationService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class CreateGeneralSubmission
{
    /**
     * Create a new loan request
     *
     * @param object $user
     * @param array $data
     * @return void
     */
    public function execute($user, $data)
    {
        $data['employee_id'] = $user->employee->id;
        $data['branch_id'] = $user->employee->branch_id;

        $generalSubmission = GeneralSubmission::create($data);

        if (!empty($data['attachment'])) {
            foreach ($data['attachment'] as $attachment) {
                $generalSubmission->attachments()->create([
                    'file_path' => $attachment->store('general-submissions', 'public'),
                    'file_name' => $attachment->getClientOriginalName(),
                    'file_type' => $attachment->getClientMimeType(),
                    'file_size' => $attachment->getSize(),
                    'file_url' => Storage::url($attachment->store('general-submissions', 'public')),
                ]);
            }
        }

        // Send push notification to admin/SPV
        $notificationService = app(NotificationService::class);
        $notificationService->notifyAdminsOnSubmission(
            'general',
            $generalSubmission->id,
            $user,
            [
                'title' => $data['title'],
                'note' => $data['note'],
                'tag' => $data['tag']
            ]
        );
    }
}
