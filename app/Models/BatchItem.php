<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\BatchItemFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $available_qty Generated column: purchased - refunded_to_provider - sold + returned_by_clients
 */
class BatchItem extends Model
{
    /** @use HasFactory<BatchItemFactory> */
    use HasFactory;

    protected $fillable = [
        'batch_id', 'product_id', 'storage_id',
        'qty_purchased', 'qty_refunded_to_provider',
        'qty_sold', 'qty_returned_by_clients',
        'purchase_price', 'sale_price',
    ];

    protected function casts(): array
    {
        return [
            'purchase_price' => 'decimal:2',
            'sale_price' => 'decimal:2',
        ];
    }

    public function batch(): BelongsTo
    {
        return $this->belongsTo(Batch::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function storage(): BelongsTo
    {
        return $this->belongsTo(Storage::class);
    }

    public function allocations(): HasMany
    {
        return $this->hasMany(OrderItemAllocation::class);
    }

    public function purchaseRefundItems(): HasMany
    {
        return $this->hasMany(PurchaseRefundItem::class);
    }

    public function scopeAvailableFor(Builder $q, int $productId): Builder
    {
        return $q->where('product_id', $productId)
            ->where('available_qty', '>', 0)
            ->orderBy('id');
    }
}
