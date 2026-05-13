<?php

declare(strict_types=1);

use App\DTO\OrderLine;
use App\DTO\OrderPayload;
use App\DTO\PurchaseLine;
use App\DTO\PurchasePayload;
use App\Models\BatchItem;
use App\Models\Product;
use App\Models\Provider;
use App\Models\Storage;
use App\Models\User;
use App\Services\OrderService;
use App\Services\PurchaseService;
use Carbon\CarbonImmutable;

beforeEach(function () {
    actingAsRole(\App\Enums\Role::WarehouseManager->value);
    $this->provider = Provider::factory()->create();
    $this->storage = Storage::factory()->create();
    $this->product = Product::factory()->create();
    $this->purchase = app(PurchaseService::class);
    $this->orders = app(OrderService::class);
});

it('deducts available stock when refunding to provider', function () {
    $batch = $this->purchase->create(new PurchasePayload(
        providerId: $this->provider->id, storageId: $this->storage->id,
        purchasedAt: CarbonImmutable::now(),
        items: [new PurchaseLine($this->product->id, 10, '5.00', '10.00')],
    ));
    $batchItem = $batch->items->first();

    $response = $this->postJson('/api/v1/provider-refunds', [
        'batch_id' => $batch->id,
        'refunded_at' => now()->toIso8601String(),
        'reason' => 'defective',
        'items' => [[
            'batch_item_id' => $batchItem->id,
            'qty' => 4,
        ]],
    ]);

    $response->assertCreated();
    $response->assertJsonPath('data.total_amount', '20.00');

    $fresh = BatchItem::find($batchItem->id);
    expect($fresh->qty_refunded_to_provider)->toBe(4);
    expect($fresh->available_qty)->toBe(6);

    $this->assertDatabaseHas('stock_movements', [
        'batch_item_id' => $batchItem->id,
        'type' => 'purchase_refund',
        'direction' => -1,
        'qty' => 4,
    ]);
    $this->assertDatabaseHas('storage_stocks', [
        'storage_id' => $this->storage->id,
        'product_id' => $this->product->id,
        'qty' => 6,
    ]);
});

it('rejects refund qty larger than available', function () {
    $user = User::factory()->create();
    $batch = $this->purchase->create(new PurchasePayload(
        providerId: $this->provider->id, storageId: $this->storage->id,
        purchasedAt: CarbonImmutable::now(),
        items: [new PurchaseLine($this->product->id, 10, '5.00', '10.00')],
    ));
    $batchItem = $batch->items->first();

    // Sell 6 of 10 — only 4 are refundable to provider
    $this->orders->create(new OrderPayload(
        userId: $user->id,
        orderedAt: CarbonImmutable::now(),
        products: [new OrderLine($this->product->id, 6)],
    ));

    $response = $this->postJson('/api/v1/provider-refunds', [
        'batch_id' => $batch->id,
        'refunded_at' => now()->toIso8601String(),
        'items' => [['batch_item_id' => $batchItem->id, 'qty' => 5]],
    ]);

    $response->assertStatus(409);
    $response->assertJsonPath('error', 'refund_exceeds_available');
    $response->assertJsonPath('available', 4);
});
