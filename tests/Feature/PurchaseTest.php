<?php

declare(strict_types=1);

use App\Enums\Role;
use App\Models\Product;
use App\Models\Provider;
use App\Models\Storage;

it('creates batch with items and increases stock', function () {
    actingAsRole(Role::WarehouseManager->value);

    $provider = Provider::factory()->create();
    $storage = Storage::factory()->create();
    $product = Product::factory()->create();

    $response = $this->postJson('/api/v1/purchases', [
        'provider_id' => $provider->id,
        'storage_id' => $storage->id,
        'purchased_at' => now()->toIso8601String(),
        'items' => [[
            'product_id' => $product->id,
            'qty' => 50,
            'purchase_price' => 10.00,
            'sale_price' => 20.00,
        ]],
    ]);

    $response->assertCreated();
    $response->assertJsonStructure(['data' => ['id', 'code', 'status', 'items']]);

    $this->assertDatabaseHas('batches', ['provider_id' => $provider->id]);
    $this->assertDatabaseHas('batch_items', [
        'product_id' => $product->id,
        'qty_purchased' => 50,
    ]);
    $this->assertDatabaseHas('stock_movements', [
        'product_id' => $product->id,
        'type' => 'purchase',
        'direction' => 1,
        'qty' => 50,
    ]);
    $this->assertDatabaseHas('storage_stocks', [
        'storage_id' => $storage->id,
        'product_id' => $product->id,
        'qty' => 50,
    ]);
});
