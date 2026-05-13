<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\BatchStatus;
use Database\Factories\BatchFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Batch extends Model
{
    /** @use HasFactory<BatchFactory> */
    use HasFactory;

    protected $fillable = [
        'provider_id', 'storage_id', 'code', 'status', 'purchased_at', 'total_cost',
    ];

    protected function casts(): array
    {
        return [
            'purchased_at' => 'datetime',
            'total_cost' => 'decimal:2',
            'status' => BatchStatus::class,
        ];
    }

    public function provider(): BelongsTo
    {
        return $this->belongsTo(Provider::class);
    }

    public function storage(): BelongsTo
    {
        return $this->belongsTo(Storage::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(BatchItem::class);
    }

    public function purchaseRefunds(): HasMany
    {
        return $this->hasMany(PurchaseRefund::class);
    }
}
