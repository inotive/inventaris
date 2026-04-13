<?php

namespace App\Actions\Data\Submission\General;

use App\Models\GeneralSubmission;
use Illuminate\Support\Facades\Storage;

class UpdateStatusGeneralSubmission
{
    /**
     * Update an existing submission
     *
     * @param object $user
     * @param GeneralSubmission $generalSubmission
     * @param array $data
     * @return void
     */
    public function execute($user, $generalSubmission, $data)
    {

        $generalSubmission->update([
            'status' => $data['status'],
            'admin_notes' => $data['admin_notes'] ?? null,
            'approved_by' => $user->id,
            'approved_at' => now(),
        ]);
    }
}
