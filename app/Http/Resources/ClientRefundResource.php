<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\ClientRefund */
class ClientRefundResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'code' => $this->code,
            'status' => $this->status->value,
            'order_id' => $this->order_id,
            'refunded_at' => $this->refunded_at?->toIso8601String(),
            'total_amount' => (string) $this->total_amount,
            'reason' => $this->reason,
            'items' => $this->whenLoaded('items', fn () => $this->items->map(fn ($i) => [
                'id' => $i->id,
                'order_item_id' => $i->order_item_id,
                'order_item_allocation_id' => $i->order_item_allocation_id,
                'qty' => $i->qty,
                'unit_sale_price' => (string) $i->unit_sale_price,
            ])),
        ];
    }
}
