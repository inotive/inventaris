<?php

namespace App\Actions\Data\Submission\Referrer;

use App\Models\Branch;

class GetBrancByRole
{
    /**
     * Get all submissions paginated by combining data from all submission types.
     *
     */
    public function execute($user)
    {
        if (!$user->hasRole('Superadmin') && $user->employee->branch_id != 2) {
            return $user->employee->branch()->get();
        }

        return Branch::select('id', 'name')->get();
    }
}
