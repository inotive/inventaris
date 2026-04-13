<?php

namespace App\Actions\Data\Submission\Procurement;

use App\Models\MaterialRequest;
use App\Models\MaterialRequestItem;
use Illuminate\Support\Facades\DB;

class UpdateProcurementRequest
{
    /**
     * Update procurement request
     *
     * @param array $data
     * @param string $materialId
     * @return void
     */
    public function execute($data, $materialId)
    {
        DB::transaction(function () use ($data, $materialId) {
            $materialRequest = MaterialRequest::findOrFail($materialId);

            $materialRequest->update(array_filter([
                'department_id' => $data['department_id'] ?? null,
                'requested_at' => $data['requested_at'] ?? null,
                'requirement' => $data['requirement'] ?? null,
                'note' => $data['note'] ?? null,
            ]));

            // Update items if provided
            if (isset($data['items'])) {
                $materialRequest->items()->delete();

                foreach ($data['items'] as $item) {
                    MaterialRequestItem::create([
                        'material_request_id' => $materialRequest->id,
                        'item_id' => $item['item_id'],
                        'quantity_requested' => $item['quantity'],
                        'note' => $item['note'] ?? null,
                    ]);
                }
            }
        });
    }
}
