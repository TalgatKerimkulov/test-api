<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PurchaseRefundItem extends Model
{
    protected $fillable = [
        'purchase_refund_id', 'batch_item_id', 'qty', 'unit_purchase_price',
    ];

    protected function casts(): array
    {
        return [
            'qty' => 'integer',
            'unit_purchase_price' => 'decimal:2',
        ];
    }

    public function purchaseRefund(): BelongsTo
    {
        return $this->belongsTo(PurchaseRefund::class);
    }

    public function batchItem(): BelongsTo
    {
        return $this->belongsTo(BatchItem::class);
    }
}
