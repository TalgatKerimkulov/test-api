<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ClientRefundItem extends Model
{
    protected $fillable = [
        'client_refund_id', 'order_item_id', 'order_item_allocation_id',
        'qty', 'unit_sale_price',
    ];

    protected function casts(): array
    {
        return [
            'qty' => 'integer',
            'unit_sale_price' => 'decimal:2',
        ];
    }

    public function clientRefund(): BelongsTo
    {
        return $this->belongsTo(ClientRefund::class);
    }

    public function orderItem(): BelongsTo
    {
        return $this->belongsTo(OrderItem::class);
    }

    public function allocation(): BelongsTo
    {
        return $this->belongsTo(OrderItemAllocation::class, 'order_item_allocation_id');
    }
}
