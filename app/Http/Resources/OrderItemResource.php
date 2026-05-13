<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\OrderItem */
class OrderItemResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'product_id' => $this->product_id,
            'qty' => $this->qty,
            'qty_refunded' => $this->qty_refunded,
            'unit_price' => (string) $this->unit_price,
            'allocations' => $this->whenLoaded('allocations', fn () => $this->allocations->map(fn ($a) => [
                'id' => $a->id,
                'batch_item_id' => $a->batch_item_id,
                'qty' => $a->qty,
                'qty_returned' => $a->qty_returned,
                'unit_purchase_price' => (string) $a->unit_purchase_price,
                'unit_sale_price' => (string) $a->unit_sale_price,
            ])),
        ];
    }
}
