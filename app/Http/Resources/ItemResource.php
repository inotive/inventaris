<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ItemResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'branch_name' => $this->branch->name,
            'code' => $this->code,
            'name' => $this->name,
            'unit' => optional($this->unit)->name,
            'min_stock' => $this->min_stock ?? 0,
            'stock' => $this->stock ?? 0,
            'price' => $this->price ?? 0,
            'description' => $this->description,
            'image_url' => $this->image_url,
            'in_month' => (float) ($this->in_month ?? 0),
            'out_month' => (float) ($this->out_month ?? 0),
            'created_at' => optional($this->created_at)->toDateTimeString(),
        ];
    }
}
