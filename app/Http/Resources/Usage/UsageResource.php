<?php

namespace App\Http\Resources\Usage;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UsageResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return  [
            'kode_usage' => $this->kode_usage,
            'date' => $this->date,
            'note' => $this->requirement,
            'request_by' => $this->requestBy ? [
                "name" => $this->requestBy->name
            ] : null,
            'items' => UsageItemResource::collection($this->item),
        ];
    }
}
