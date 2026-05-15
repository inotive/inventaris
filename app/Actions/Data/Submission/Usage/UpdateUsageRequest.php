<?php

namespace App\Actions\Data\Submission\Usage;

use App\Models\GoodIssue;

class UpdateUsageRequest
{
    /**
     * Get submission statistics based on type and status.
     *
     * @param array $data
     * @param string $usageId
     * @return void
     */
    public function execute($data, $usageId)
    {
        $goodIssue = GoodIssue::findOrFail($usageId);
        $goodIssue->update(array_filter([
            'date' => $data['date'] ?? null,
            'requirement' => $data['notes'] ?? null,
        ]));

        $goodIssue->items()->delete();

        foreach ($data['items'] as $item) {
            $goodIssue->items()->create([
                'item_id' => $item['id'],
                'quantity_issued' => $item['quantity'],
                'note' => $item['notes'] ?? null,
            ]);
        }
    }
}
