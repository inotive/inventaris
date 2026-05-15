<?php

namespace App\Actions\Data\Submission\Loan;

use App\Models\Receivable;
use App\Services\NotificationService;
use Illuminate\Support\Facades\DB;

class CreateLoanRequest
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
        DB::transaction(function () use ($user, $data) {
            if (isset($data['file_path'])) {
                $file_path = $data['file_path']->store('receivables', 'public');
                $data['file_path'] = $file_path;
            }

            $receivable = Receivable::create([
                'amount' => $data['amount'],
                'date' => now()->format('Y-m-d'),
                'tenor' => $data['tenor'],
                'note' => $data['note'] ?? null,
                'file_path' => $data['file_path'] ?? null,
                'status' => 'pending',
                'request_by' => $user->employee ? $user->employee->id : null,
            ]);

            // Send push notification to admin/SPV
            $notificationService = app(NotificationService::class);
            $notificationService->notifyAdminsOnSubmission(
                'loan',
                $receivable->id,
                $user,
                [
                    'amount' => number_format($data['amount'], 0, ',', '.'),
                    'tenor' => $data['tenor']
                ]
            );
        });
    }
}
