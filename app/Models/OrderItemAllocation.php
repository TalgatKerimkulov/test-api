<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderItemAllocation extends Model
{
    protected $fillable = [
        'order_item_id', 'batch_item_id',
        'qty', 'qty_returned',
        'unit_purchase_price', 'unit_sale_price',
    ];

    protected function casts(): array
    {
        return [
            'qty' => 'integer',
            'qty_returned' => 'integer',
            'unit_purchase_price' => 'decimal:2',
            'unit_sale_price' => 'decimal:2',
        ];
    }

    public function orderItem(): BelongsTo
    {
        return $this->belongsTo(OrderItem::class);
    }

    public function batchItem(): BelongsTo
    {
        return $this->belongsTo(BatchItem::class);
    }
}
