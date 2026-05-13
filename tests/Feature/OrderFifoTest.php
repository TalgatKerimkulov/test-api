<?php

declare(strict_types=1);

use App\DTO\PurchaseLine;
use App\DTO\PurchasePayload;
use App\Models\Product;
use App\Models\Provider;
use App\Models\Storage;
use App\Models\User;
use App\Services\PurchaseService;
use Carbon\CarbonImmutable;

beforeEach(function () {
    actingAsRole(\App\Enums\Role::Manager->value);
    $this->provider = Provider::factory()->create();
    $this->storage = Storage::factory()->create();
    $this->product = Product::factory()->create();
    $this->user = User::factory()->create();
    $this->purchase = app(PurchaseService::class);
});

it('splits qty across multiple batches using FIFO', function () {
    $this->purchase->create(new PurchasePayload(
        providerId: $this->provider->id, storageId: $this->storage->id,
        purchasedAt: CarbonImmutable::now()->subDays(3),
        items: [new PurchaseLine($this->product->id, 5, '50.00', '80.00')],
    ));
    $this->purchase->create(new PurchasePayload(
        providerId: $this->provider->id, storageId: $this->storage->id,
        purchasedAt: CarbonImmutable::now()->subDays(2),
        items: [new PurchaseLine($this->product->id, 7, '52.00', '80.00')],
    ));
    $this->purchase->create(new PurchasePayload(
        providerId: $this->provider->id, storageId: $this->storage->id,
        purchasedAt: CarbonImmutable::now()->subDay(),
        items: [new PurchaseLine($this->product->id, 20, '55.00', '85.00')],
    ));

    $response = $this->postJson('/api/v1/client-orders', [
        'user_id' => $this->user->id,
        'ordered_at' => now()->toIso8601String(),
        'products' => [['id' => $this->product->id, 'qty' => 15]],
    ]);

    $response->assertCreated();

    $allocations = $response->json('data.items.0.allocations');
    expect($allocations)->toHaveCount(3);
    expect($allocations[0]['qty'])->toBe(5);
    expect($allocations[1]['qty'])->toBe(7);
    expect($allocations[2]['qty'])->toBe(3);

    $this->assertDatabaseHas('batch_items', [
        'product_id' => $this->product->id,
        'qty_purchased' => 5,
        'qty_sold' => 5,
    ]);
    $this->assertDatabaseHas('batch_items', [
        'product_id' => $this->product->id,
        'qty_purchased' => 7,
        'qty_sold' => 7,
    ]);
    $this->assertDatabaseHas('batch_items', [
        'product_id' => $this->product->id,
        'qty_purchased' => 20,
        'qty_sold' => 3,
    ]);
});

it('fails with 409 when stock is insufficient', function () {
    $response = $this->postJson('/api/v1/client-orders', [
        'user_id' => $this->user->id,
        'products' => [['id' => $this->product->id, 'qty' => 1]],
    ]);

    $response->assertStatus(409);
    $response->assertJsonPath('error', 'insufficient_stock');
    $response->assertJsonPath('requested', 1);
    $response->assertJsonPath('available', 0);

    $this->assertDatabaseCount('orders', 0);
});

it('rolls back the entire order when one product is short', function () {
    $second = Product::factory()->create();
    $this->purchase->create(new PurchasePayload(
        providerId: $this->provider->id, storageId: $this->storage->id,
        purchasedAt: CarbonImmutable::now(),
        items: [
            new PurchaseLine($this->product->id, 10, '50.00', '80.00'),
        ],
    ));

    $response = $this->postJson('/api/v1/client-orders', [
        'user_id' => $this->user->id,
        'products' => [
            ['id' => $this->product->id, 'qty' => 5],
            ['id' => $second->id, 'qty' => 1],
        ],
    ]);

    $response->assertStatus(409);
    $this->assertDatabaseCount('orders', 0);
    $this->assertDatabaseHas('batch_items', [
        'product_id' => $this->product->id,
        'qty_sold' => 0,
    ]);
});
