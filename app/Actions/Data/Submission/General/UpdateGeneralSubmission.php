<?php

namespace App\Actions\Data\Submission\General;

use App\Models\GeneralSubmission;
use Illuminate\Support\Facades\Storage;

class UpdateGeneralSubmission
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
        // dd($data);
        $generalSubmission->update($data);

        if (!empty($data['attachment'])) {
            // Delete existing attachments
            foreach ($generalSubmission->attachments as $existingAttachment) {
                if ($existingAttachment->file_path && Storage::disk('public')->exists($existingAttachment->file_path)) {
                    Storage::disk('public')->delete($existingAttachment->file_path);
                }
                $existingAttachment->delete();
            }

            // Create new attachments
            foreach ($data['attachment'] as $attachment) {
                $filePath = $attachment->store('general-submissions', 'public');

                $generalSubmission->attachments()->create([
                    'file_path' => $filePath,
                    'file_name' => $attachment->getClientOriginalName(),
                    'file_type' => $attachment->getClientMimeType(),
                    'file_size' => $attachment->getSize(),
                    'file_url' => Storage::url($filePath),
                ]);
            }
        }
    }
}
