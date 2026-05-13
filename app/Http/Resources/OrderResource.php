<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\Order */
class OrderResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'code' => $this->code,
            'status' => $this->status->value,
            'user_id' => $this->user_id,
            'ordered_at' => $this->ordered_at?->toIso8601String(),
            'total_amount' => (string) $this->total_amount,
            'items' => OrderItemResource::collection($this->whenLoaded('items')),
        ];
    }
}
