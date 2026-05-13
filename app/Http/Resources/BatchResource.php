<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\Batch */
class BatchResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'code' => $this->code,
            'status' => $this->status->value,
            'provider_id' => $this->provider_id,
            'storage_id' => $this->storage_id,
            'purchased_at' => $this->purchased_at?->toIso8601String(),
            'total_cost' => (string) $this->total_cost,
            'items' => BatchItemResource::collection($this->whenLoaded('items')),
        ];
    }
}
