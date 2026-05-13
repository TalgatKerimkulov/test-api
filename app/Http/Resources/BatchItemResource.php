<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\BatchItem */
class BatchItemResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'product_id' => $this->product_id,
            'storage_id' => $this->storage_id,
            'qty_purchased' => $this->qty_purchased,
            'qty_refunded_to_provider' => $this->qty_refunded_to_provider,
            'qty_sold' => $this->qty_sold,
            'qty_returned_by_clients' => $this->qty_returned_by_clients,
            'available_qty' => $this->available_qty,
            'purchase_price' => (string) $this->purchase_price,
            'sale_price' => (string) $this->sale_price,
        ];
    }
}
