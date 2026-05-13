<?php

declare(strict_types=1);

use App\DTO\ClientRefundLine;
use App\DTO\ClientRefundPayload;
use App\DTO\OrderLine;
use App\DTO\OrderPayload;
use App\DTO\PurchaseLine;
use App\DTO\PurchasePayload;
use App\Models\Product;
use App\Models\Provider;
use App\Models\Storage;
use App\Models\User;
use App\Services\ClientRefundService;
use App\Services\OrderService;
use App\Services\PurchaseService;
use Carbon\CarbonImmutable;

beforeEach(function () {
    actingAsRole(\App\Enums\Role::Accountant->value);
    $this->user = User::factory()->create();
    $this->provider = Provider::factory()->create();
    $this->storage = Storage::factory()->create();
    $this->product = Product::factory()->create();
    $this->purchase = app(PurchaseService::class);
    $this->orders = app(OrderService::class);
    $this->refunds = app(ClientRefundService::class);
});

it('returns available products with correct qty and price', function () {
    $this->purchase->create(new PurchasePayload(
        providerId: $this->provider->id, storageId: $this->storage->id,
        purchasedAt: CarbonImmutable::now(),
        items: [new PurchaseLine($this->product->id, 10, '5.00', '10.00')],
    ));

    $response = $this->getJson('/api/v1/products/available');
    $response->assertOk();
    $response->assertJsonCount(1, 'data');
    $response->assertJsonPath('data.0.id', $this->product->id);
    $response->assertJsonPath('data.0.qty', 10);
    $response->assertJsonPath('data.0.price', '10.00');
});

it('hides product from available list when fully sold', function () {
    $this->purchase->create(new PurchasePayload(
        providerId: $this->provider->id, storageId: $this->storage->id,
        purchasedAt: CarbonImmutable::now(),
        items: [new PurchaseLine($this->product->id, 5, '5.00', '10.00')],
    ));

    $this->orders->create(new OrderPayload(
        userId: $this->user->id,
        orderedAt: CarbonImmutable::now(),
        products: [new OrderLine($this->product->id, 5)],
    ));

    $response = $this->getJson('/api/v1/products/available');
    $response->assertOk();
    $response->assertJsonCount(0, 'data');
});

it('computes remaining quantities by date', function () {
    $this->purchase->create(new PurchasePayload(
        providerId: $this->provider->id, storageId: $this->storage->id,
        purchasedAt: CarbonImmutable::parse('2026-05-01T10:00:00Z'),
        items: [new PurchaseLine($this->product->id, 10, '5.00', '10.00')],
    ));

    $this->orders->create(new OrderPayload(
        userId: $this->user->id,
        orderedAt: CarbonImmutable::parse('2026-05-10T10:00:00Z'),
        products: [new OrderLine($this->product->id, 3)],
    ));

    // As of 2026-05-05: only purchase happened — qty=10
    $resp1 = $this->getJson('/api/v1/storages/remaining-quantities?date=2026-05-05');
    $resp1->assertOk();
    $resp1->assertJsonPath('data.0.qty', 10);

    // As of 2026-05-12: purchase + sale — qty=7
    $resp2 = $this->getJson('/api/v1/storages/remaining-quantities?date=2026-05-12');
    $resp2->assertOk();
    $resp2->assertJsonPath('data.0.qty', 7);
});

it('calculates batch profit including client refunds', function () {
    // Purchase 10 @ 5.00, sell 10 @ 10.00 → gross 100, cost 50, profit 50
    $batch = $this->purchase->create(new PurchasePayload(
        providerId: $this->provider->id, storageId: $this->storage->id,
        purchasedAt: CarbonImmutable::now(),
        items: [new PurchaseLine($this->product->id, 10, '5.00', '10.00')],
    ));

    $order = $this->orders->create(new OrderPayload(
        userId: $this->user->id,
        orderedAt: CarbonImmutable::now(),
        products: [new OrderLine($this->product->id, 10)],
    ));

    $response1 = $this->getJson('/api/v1/batches/profit?batch_id='.$batch->id);
    $response1->assertOk();
    expect((float) $response1->json('data.0.profit'))->toBe(50.0);
    expect((float) $response1->json('data.0.gross_sales'))->toBe(100.0);

    // Now client returns 3 — effectively_sold = 7, gross stays 100,
    // refund_loss = 30, purchase_cost_for_sold = 7*5=35 → profit = 100 - 30 - 35 = 35
    $this->refunds->create(new ClientRefundPayload(
        orderId: $order->id,
        refundedAt: CarbonImmutable::now(),
        reason: null,
        items: [new ClientRefundLine($order->items->first()->id, 3)],
    ));

    $response2 = $this->getJson('/api/v1/batches/profit?batch_id='.$batch->id);
    $response2->assertOk();
    expect((float) $response2->json('data.0.profit'))->toBe(35.0);
    expect((float) $response2->json('data.0.client_refund_loss'))->toBe(30.0);
});
