<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ItemMovementResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'item_id' => $this->item_id,
            'warehouse' => $this->whenLoaded('warehouse', function () {
                return [
                    'id' => optional($this->warehouse)->id,
                    'name' => optional($this->warehouse)->name,
                ];
            }),
            'type' => $this->type,
            'quantity' => (float) $this->quantity,
            'reference_type' => $this->reference_type,
            'reference_id' => $this->reference_id,
            'last_stock' => (float) $this->last_stock,
            'current_stock' => (float) $this->current_stock,
            'notes' => $this->notes,
            'created_at' => optional($this->created_at)->toDateTimeString(),
        ];
    }
}
