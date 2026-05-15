<?php

namespace App\Actions\Data\Submission\Loan;

use App\Models\Receivable;
use Illuminate\Support\Facades\DB;

class UpdateLoanRequest
{
    /**
     * Update an existing loan request
     *
     * @param array $data
     * @param string $loanId
     * @return void
     */
    public function execute($data, $loanId)
    {
        DB::transaction(function () use ($data, $loanId) {
            $receivable = Receivable::findOrFail($loanId);

            if (isset($data['file_path'])) {
                $file_path = $data['file_path']->store('receivables', 'public');
                $data['file_path'] = $file_path;
            }

            $receivable->update(array_filter([
                'amount' => $data['amount'] ?? null,
                'date' => $data['date'] ?? null,
                'tenor' => $data['tenor'] ?? null,
                'note' => $data['note'] ?? null,
                'file_path' => $data['file_path'] ?? null,
            ], function ($value) {
                return $value !== null;
            }));
        });
    }
}
