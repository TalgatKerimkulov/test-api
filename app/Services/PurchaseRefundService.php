<?php

declare(strict_types=1);

namespace App\Services;

use App\DTO\PurchaseRefundPayload;
use App\Enums\BatchStatus;
use App\Enums\RefundStatus;
use App\Enums\StockMovementType;
use App\Exceptions\BatchRefundMismatchException;
use App\Exceptions\RefundExceedsAvailableException;
use App\Models\Batch;
use App\Models\BatchItem;
use App\Models\PurchaseRefund;
use App\Models\PurchaseRefundItem;
use Illuminate\Support\Facades\DB;

final class PurchaseRefundService
{
    public function __construct(
        private readonly StockService $stocks,
        private readonly CodeGenerator $codes,
    ) {}

    public function create(PurchaseRefundPayload $data): PurchaseRefund
    {
        return DB::transaction(function () use ($data) {
            /** @var Batch $batch */
            $batch = Batch::query()->lockForUpdate()->findOrFail($data->batchId);

            $refund = PurchaseRefund::create([
                'batch_id' => $batch->id,
                'code' => $this->codes->generate('PRF'),
                'status' => RefundStatus::Completed,
                'reason' => $data->reason,
                'refunded_at' => $data->refundedAt,
                'total_amount' => 0,
            ]);

            $total = '0';

            foreach ($data->items as $line) {
                /** @var BatchItem $bi */
                $bi = BatchItem::query()->lockForUpdate()->findOrFail($line->batchItemId);

                if ($bi->batch_id !== $batch->id) {
                    throw new BatchRefundMismatchException(
                        batchId: $batch->id,
                        batchItemId: $bi->id,
                    );
                }

                if ($line->qty > (int) $bi->available_qty) {
                    throw new RefundExceedsAvailableException(
                        batchItemId: $bi->id,
                        requested: $line->qty,
                        available: (int) $bi->available_qty,
                    );
                }

                $bi->increment('qty_refunded_to_provider', $line->qty);

                PurchaseRefundItem::create([
                    'purchase_refund_id' => $refund->id,
                    'batch_item_id' => $bi->id,
                    'qty' => $line->qty,
                    'unit_purchase_price' => $bi->purchase_price,
                ]);

                $this->stocks->applyMovement(
                    productId: $bi->product_id,
                    storageId: $bi->storage_id,
                    batchItemId: $bi->id,
                    type: StockMovementType::PurchaseRefund,
                    direction: -1,
                    qty: $line->qty,
                    reference: $refund,
                    occurredAt: $data->refundedAt,
                );

                $total = bcadd($total, bcmul((string) $line->qty, (string) $bi->purchase_price, 2), 2);
            }

            $refund->update(['total_amount' => $total]);
            $this->refreshBatchStatus($batch);

            return $refund->load('items');
        });
    }

    private function refreshBatchStatus(Batch $batch): void
    {
        $batch->refresh();

        $items = $batch->items()->get();
        $purchased = (int) $items->sum('qty_purchased');
        $refunded = (int) $items->sum('qty_refunded_to_provider');

        if ($refunded === 0) {
            return;
        }

        $status = $refunded >= $purchased ? BatchStatus::Refunded : BatchStatus::PartiallyRefunded;
        $batch->update(['status' => $status]);
    }
}
