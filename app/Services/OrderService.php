<?php

declare(strict_types=1);

namespace App\Services;

use App\DTO\OrderPayload;
use App\Enums\OrderStatus;
use App\Enums\StockMovementType;
use App\Exceptions\InsufficientStockException;
use App\Models\BatchItem;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderItemAllocation;
use Illuminate\Support\Facades\DB;

final class OrderService
{
    public function __construct(
        private readonly StockService $stocks,
        private readonly CodeGenerator $codes,
    ) {}

    public function create(OrderPayload $data): Order
    {
        return DB::transaction(function () use ($data) {
            $order = Order::create([
                'user_id' => $data->userId,
                'code' => $this->codes->generate('ORD'),
                'status' => OrderStatus::Completed,
                'ordered_at' => $data->orderedAt,
                'total_amount' => 0,
            ]);

            $total = '0';

            foreach ($data->products as $line) {
                $remaining = $line->qty;

                // Lock all available batch_items for this product.
                // Using FOR UPDATE (not SKIP LOCKED): we want to see the true total
                // and serialize against other orders.
                $items = BatchItem::query()
                    ->where('product_id', $line->productId)
                    ->where('available_qty', '>', 0)
                    ->orderBy('id') // FIFO by insertion (= by batch creation)
                    ->lockForUpdate()
                    ->get();

                $available = (int) $items->sum('available_qty');
                if ($available < $remaining) {
                    throw new InsufficientStockException(
                        productId: $line->productId,
                        requested: $line->qty,
                        available: $available,
                    );
                }

                $unitPrice = (string) $items->first()->sale_price;

                $orderItem = OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $line->productId,
                    'qty' => $line->qty,
                    'qty_refunded' => 0,
                    'unit_price' => $unitPrice,
                ]);

                foreach ($items as $bi) {
                    if ($remaining === 0) {
                        break;
                    }
                    $take = min($remaining, (int) $bi->available_qty);
                    if ($take <= 0) {
                        continue;
                    }

                    OrderItemAllocation::create([
                        'order_item_id' => $orderItem->id,
                        'batch_item_id' => $bi->id,
                        'qty' => $take,
                        'qty_returned' => 0,
                        'unit_purchase_price' => $bi->purchase_price,
                        'unit_sale_price' => $bi->sale_price,
                    ]);

                    $bi->increment('qty_sold', $take);

                    $this->stocks->applyMovement(
                        productId: $bi->product_id,
                        storageId: $bi->storage_id,
                        batchItemId: $bi->id,
                        type: StockMovementType::Sale,
                        direction: -1,
                        qty: $take,
                        reference: $orderItem,
                        occurredAt: $data->orderedAt,
                    );

                    $remaining -= $take;
                }

                $total = bcadd($total, bcmul((string) $line->qty, $unitPrice, 2), 2);
            }

            $order->update(['total_amount' => $total]);

            return $order->load('items.allocations');
        });
    }
}
