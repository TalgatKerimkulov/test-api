<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\PurchaseRefund */
class PurchaseRefundResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'code' => $this->code,
            'status' => $this->status->value,
            'batch_id' => $this->batch_id,
            'refunded_at' => $this->refunded_at?->toIso8601String(),
            'total_amount' => (string) $this->total_amount,
            'reason' => $this->reason,
            'items' => $this->whenLoaded('items', fn () => $this->items->map(fn ($i) => [
                'id' => $i->id,
                'batch_item_id' => $i->batch_item_id,
                'qty' => $i->qty,
                'unit_purchase_price' => (string) $i->unit_purchase_price,
            ])),
        ];
    }
}
