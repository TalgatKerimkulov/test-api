<?php

declare(strict_types=1);

use App\DTO\OrderLine;
use App\DTO\OrderPayload;
use App\DTO\PurchaseLine;
use App\DTO\PurchasePayload;
use App\Models\BatchItem;
use App\Models\Order;
use App\Models\Product;
use App\Models\Provider;
use App\Models\Storage;
use App\Models\User;
use App\Services\OrderService;
use App\Services\PurchaseService;
use Carbon\CarbonImmutable;

beforeEach(function () {
    actingAsRole(\App\Enums\Role::Manager->value);
    $this->user = User::factory()->create();
    $this->provider = Provider::factory()->create();
    $this->storage = Storage::factory()->create();
    $this->product = Product::factory()->create();
    $this->purchase = app(PurchaseService::class);
    $this->orders = app(OrderService::class);
});

it('restores stock and links refund to allocation', function () {
    $this->purchase->create(new PurchasePayload(
        providerId: $this->provider->id, storageId: $this->storage->id,
        purchasedAt: CarbonImmutable::now(),
        items: [new PurchaseLine($this->product->id, 10, '5.00', '10.00')],
    ));

    $order = $this->orders->create(new OrderPayload(
        userId: $this->user->id,
        orderedAt: CarbonImmutable::now(),
        products: [new OrderLine($this->product->id, 6)],
    ));
    $orderItem = $order->items->first();
    $allocation = $orderItem->allocations->first();

    $response = $this->postJson('/api/v1/client-refunds', [
        'order_id' => $order->id,
        'refunded_at' => now()->toIso8601String(),
        'reason' => 'changed mind',
        'items' => [['order_item_id' => $orderItem->id, 'qty' => 2]],
    ]);

    $response->assertCreated();
    $response->assertJsonPath('data.total_amount', '20.00');

    $this->assertDatabaseHas('client_refund_items', [
        'order_item_id' => $orderItem->id,
        'order_item_allocation_id' => $allocation->id,
        'qty' => 2,
    ]);

    $batchItem = BatchItem::query()->where('product_id', $this->product->id)->first();
    expect($batchItem->qty_returned_by_clients)->toBe(2);
    expect($batchItem->available_qty)->toBe(6); // 10 - 6 sold + 2 returned

    $this->assertDatabaseHas('stock_movements', [
        'type' => 'client_refund',
        'direction' => 1,
        'qty' => 2,
    ]);

    Order::query()->find($order->id)->refresh();
    $this->assertDatabaseHas('orders', [
        'id' => $order->id,
        'status' => 'partially_refunded',
    ]);
});

it('rejects refund qty larger than purchased', function () {
    $this->purchase->create(new PurchasePayload(
        providerId: $this->provider->id, storageId: $this->storage->id,
        purchasedAt: CarbonImmutable::now(),
        items: [new PurchaseLine($this->product->id, 10, '5.00', '10.00')],
    ));

    $order = $this->orders->create(new OrderPayload(
        userId: $this->user->id,
        orderedAt: CarbonImmutable::now(),
        products: [new OrderLine($this->product->id, 3)],
    ));
    $orderItem = $order->items->first();

    $response = $this->postJson('/api/v1/client-refunds', [
        'order_id' => $order->id,
        'refunded_at' => now()->toIso8601String(),
        'items' => [['order_item_id' => $orderItem->id, 'qty' => 5]],
    ]);

    $response->assertStatus(409);
    $response->assertJsonPath('error', 'refund_exceeds_sold');
    $response->assertJsonPath('refundable', 3);
});
