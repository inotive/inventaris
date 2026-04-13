<?php

namespace App\Actions\Data\Submission\Usage;

use App\Models\GoodIssue;

class GetDetailSubmission
{
    /**
     * Get detail submission.
     *
     * @param string|null $kode_usage
     * @return void
     */
    public function execute($kode_usage)
    {
        $goodIssue = GoodIssue::where('kode_usage', $kode_usage)->with(['item', 'requestBy:id,name'])->firstOrFail();
        return $goodIssue;
    }
}
