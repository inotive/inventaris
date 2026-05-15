<?php

namespace App\Actions\Data\Submission\Usage;

use App\Models\GoodIssue;

class GetListUsageRequest
{
    /**
     * Get list usage request.
     *
     * @return void
     */
    public function execute($data)
    {
        $goodIssue = GoodIssue::latest();

        $goodIssue->when(isset($data['search']), function ($query) use ($data) {
            $query->where('kode_usage', 'like', "%{$data['search']}%")
                ->orWhere('requirement', 'like', "%{$data['search']}%");
        });

        $goodIssue->when(isset($data['status']), function ($query) use ($data) {
            $query->where('status', $data['status']);
        });


        if (isset($data['limit'])) {
            return $goodIssue->limit($data['limit'])->get();
        }else{
            return $goodIssue->cursorPaginate(10)->withQueryString();
        }
    }
}
