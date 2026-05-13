<?php

declare(strict_types=1);

namespace App\Services;

use App\DTO\PurchasePayload;
use App\Enums\BatchStatus;
use App\Enums\StockMovementType;
use App\Models\Batch;
use App\Models\BatchItem;
use Illuminate\Support\Facades\DB;

final class PurchaseService
{
    public function __construct(
        private readonly StockService $stocks,
        private readonly CodeGenerator $codes,
    ) {}

    public function create(PurchasePayload $data): Batch
    {
        return DB::transaction(function () use ($data) {
            $batch = Batch::create([
                'provider_id' => $data->providerId,
                'storage_id' => $data->storageId,
                'code' => $this->codes->generate('BATCH'),
                'status' => BatchStatus::Completed,
                'purchased_at' => $data->purchasedAt,
                'total_cost' => 0,
            ]);

            $total = '0';

            foreach ($data->items as $line) {
                $item = BatchItem::create([
                    'batch_id' => $batch->id,
                    'product_id' => $line->productId,
                    'storage_id' => $data->storageId,
                    'qty_purchased' => $line->qty,
                    'qty_refunded_to_provider' => 0,
                    'qty_sold' => 0,
                    'qty_returned_by_clients' => 0,
                    'purchase_price' => $line->purchasePrice,
                    'sale_price' => $line->salePrice,
                ]);

                $this->stocks->applyMovement(
                    productId: $item->product_id,
                    storageId: $item->storage_id,
                    batchItemId: $item->id,
                    type: StockMovementType::Purchase,
                    direction: 1,
                    qty: $line->qty,
                    reference: $batch,
                    occurredAt: $data->purchasedAt,
                );

                $total = bcadd($total, bcmul((string) $line->qty, $line->purchasePrice, 2), 2);
            }

            $batch->update(['total_cost' => $total]);

            return $batch->load('items');
        });
    }
}
