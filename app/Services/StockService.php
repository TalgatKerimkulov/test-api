<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\StockMovementType;
use App\Models\StockMovement;
use App\Models\StorageStock;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

final class StockService
{
    /**
     * Records a stock movement and synchronises the denormalized storage_stocks row.
     *
     * Must be called inside an active DB::transaction by the caller, since
     * the surrounding domain operation is responsible for atomicity guarantees.
     */
    public function applyMovement(
        int $productId,
        int $storageId,
        ?int $batchItemId,
        StockMovementType $type,
        int $direction,
        int $qty,
        ?Model $reference,
        CarbonInterface $occurredAt,
    ): StockMovement {
        $movement = StockMovement::create([
            'product_id' => $productId,
            'storage_id' => $storageId,
            'batch_item_id' => $batchItemId,
            'type' => $type,
            'direction' => $direction,
            'qty' => $qty,
            'reference_type' => $reference?->getMorphClass(),
            'reference_id' => $reference?->getKey(),
            'occurred_at' => $occurredAt,
        ]);

        $stock = StorageStock::lockForUpdate()->firstOrCreate(
            ['storage_id' => $storageId, 'product_id' => $productId],
            ['qty' => 0],
        );

        $stock->qty += $direction * $qty;
        $stock->save();

        return $movement;
    }

    /**
     * Stock balances per (storage, product) as of the end of the given day.
     */
    public function remainingOnDate(CarbonInterface $date, ?int $storageId = null): Collection
    {
        $endOfDay = $date->copy()->endOfDay();

        return DB::table('stock_movements as sm')
            ->join('products as p', 'p.id', '=', 'sm.product_id')
            ->join('storages as s', 's.id', '=', 'sm.storage_id')
            ->selectRaw('sm.storage_id, s.name as storage_name,
                         sm.product_id, p.name as product_name,
                         COALESCE(SUM(sm.qty * sm.direction), 0) as qty')
            ->where('sm.occurred_at', '<=', $endOfDay)
            ->when($storageId, fn ($q, $id) => $q->where('sm.storage_id', $id))
            ->groupBy('sm.storage_id', 's.name', 'sm.product_id', 'p.name')
            ->havingRaw('COALESCE(SUM(sm.qty * sm.direction), 0) <> 0')
            ->orderBy('sm.storage_id')
            ->orderBy('p.name')
            ->get();
    }
}
