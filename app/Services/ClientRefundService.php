<?php

declare(strict_types=1);

namespace App\Services;

use App\DTO\ClientRefundPayload;
use App\Enums\OrderStatus;
use App\Enums\RefundStatus;
use App\Enums\StockMovementType;
use App\Exceptions\ClientRefundExceedsSoldException;
use App\Exceptions\OrderMismatchException;
use App\Models\BatchItem;
use App\Models\ClientRefund;
use App\Models\ClientRefundItem;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Support\Facades\DB;

final class ClientRefundService
{
    public function __construct(
        private readonly StockService $stocks,
        private readonly CodeGenerator $codes,
    ) {}

    public function create(ClientRefundPayload $data): ClientRefund
    {
        return DB::transaction(function () use ($data) {
            /** @var Order $order */
            $order = Order::query()->lockForUpdate()->findOrFail($data->orderId);

            $refund = ClientRefund::create([
                'order_id' => $order->id,
                'code' => $this->codes->generate('CRF'),
                'status' => RefundStatus::Completed,
                'reason' => $data->reason,
                'refunded_at' => $data->refundedAt,
                'total_amount' => 0,
            ]);

            $total = '0';

            foreach ($data->items as $line) {
                /** @var OrderItem $orderItem */
                $orderItem = OrderItem::query()->lockForUpdate()->findOrFail($line->orderItemId);

                if ($orderItem->order_id !== $order->id) {
                    throw new OrderMismatchException(
                        orderId: $order->id,
                        orderItemId: $orderItem->id,
                    );
                }

                $refundable = (int) $orderItem->qty - (int) $orderItem->qty_refunded;
                if ($line->qty > $refundable) {
                    throw new ClientRefundExceedsSoldException(
                        orderItemId: $orderItem->id,
                        requested: $line->qty,
                        refundable: $refundable,
                    );
                }

                $remaining = $line->qty;

                $allocations = $orderItem->allocations()
                    ->orderBy('id')
                    ->lockForUpdate()
                    ->get();

                foreach ($allocations as $alloc) {
                    if ($remaining === 0) {
                        break;
                    }
                    $available = (int) $alloc->qty - (int) $alloc->qty_returned;
                    if ($available <= 0) {
                        continue;
                    }
                    $take = min($remaining, $available);

                    $alloc->increment('qty_returned', $take);

                    /** @var BatchItem $bi */
                    $bi = BatchItem::query()->lockForUpdate()->findOrFail($alloc->batch_item_id);
                    $bi->increment('qty_returned_by_clients', $take);

                    ClientRefundItem::create([
                        'client_refund_id' => $refund->id,
                        'order_item_id' => $orderItem->id,
                        'order_item_allocation_id' => $alloc->id,
                        'qty' => $take,
                        'unit_sale_price' => $alloc->unit_sale_price,
                    ]);

                    $this->stocks->applyMovement(
                        productId: $bi->product_id,
                        storageId: $bi->storage_id,
                        batchItemId: $bi->id,
                        type: StockMovementType::ClientRefund,
                        direction: 1,
                        qty: $take,
                        reference: $refund,
                        occurredAt: $data->refundedAt,
                    );

                    $total = bcadd($total, bcmul((string) $take, (string) $alloc->unit_sale_price, 2), 2);
                    $remaining -= $take;
                }

                $orderItem->increment('qty_refunded', $line->qty);
            }

            $refund->update(['total_amount' => $total]);
            $this->refreshOrderStatus($order);

            return $refund->load('items');
        });
    }

    private function refreshOrderStatus(Order $order): void
    {
        $order->refresh();
        $items = $order->items()->get();
        $sold = (int) $items->sum('qty');
        $refunded = (int) $items->sum('qty_refunded');

        if ($refunded === 0) {
            return;
        }

        $status = $refunded >= $sold ? OrderStatus::Refunded : OrderStatus::PartiallyRefunded;
        $order->update(['status' => $status]);
    }
}
