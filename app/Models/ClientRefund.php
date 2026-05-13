<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\RefundStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ClientRefund extends Model
{
    protected $fillable = [
        'order_id', 'code', 'status', 'reason', 'refunded_at', 'total_amount',
    ];

    protected function casts(): array
    {
        return [
            'refunded_at' => 'datetime',
            'total_amount' => 'decimal:2',
            'status' => RefundStatus::class,
        ];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(ClientRefundItem::class);
    }
}
