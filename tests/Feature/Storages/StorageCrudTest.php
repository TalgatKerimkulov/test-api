<?php

declare(strict_types=1);

use App\DTO\PurchaseLine;
use App\DTO\PurchasePayload;
use App\Models\Product;
use App\Models\Provider;
use App\Models\Storage;
use App\Services\PurchaseService;
use Carbon\CarbonImmutable;

it('admin can create a storage', function () {
    actingAsAdmin();

    $response = $this->postJson('/api/v1/storages', [
        'name' => 'Main Storage',
        'address' => 'Tashkent, Chilanzar',
    ]);

    $response->assertCreated();
    $this->assertDatabaseHas('storages', ['name' => 'Main Storage']);
});

it('cannot delete a storage that has stock', function () {
    actingAsAdmin();
    $provider = Provider::factory()->create();
    $storage = Storage::factory()->create();
    $product = Product::factory()->create();
    app(PurchaseService::class)->create(new PurchasePayload(
        providerId: $provider->id, storageId: $storage->id,
        purchasedAt: CarbonImmutable::now(),
        items: [new PurchaseLine($product->id, 5, '5.00', '10.00')],
    ));

    $response = $this->deleteJson("/api/v1/storages/{$storage->id}");
    $response->assertStatus(409);
});
