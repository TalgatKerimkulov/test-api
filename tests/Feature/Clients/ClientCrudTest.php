<?php

declare(strict_types=1);

use App\DTO\OrderLine;
use App\DTO\OrderPayload;
use App\DTO\PurchaseLine;
use App\DTO\PurchasePayload;
use App\Enums\Role;
use App\Enums\UserType;
use App\Models\Product;
use App\Models\Provider;
use App\Models\Storage;
use App\Models\User;
use App\Services\OrderService;
use App\Services\PurchaseService;
use Carbon\CarbonImmutable;

it('manager can create a client', function () {
    actingAsRole(Role::Manager->value);

    $response = $this->postJson('/api/v1/clients', [
        'name' => 'Market 1',
        'phone' => '+998901234567',
        'email' => 'client@example.com',
        'address' => 'Tashkent',
    ]);

    $response->assertCreated();
    $this->assertDatabaseHas('users', [
        'email' => 'client@example.com',
        'type' => UserType::Client->value,
    ]);
});

it('cannot delete a client who has orders', function () {
    actingAsAdmin();
    $client = User::factory()->create(['type' => UserType::Client->value]);
    $provider = Provider::factory()->create();
    $storage = Storage::factory()->create();
    $product = Product::factory()->create();
    app(PurchaseService::class)->create(new PurchasePayload(
        providerId: $provider->id, storageId: $storage->id,
        purchasedAt: CarbonImmutable::now(),
        items: [new PurchaseLine($product->id, 5, '5.00', '10.00')],
    ));
    app(OrderService::class)->create(new OrderPayload(
        userId: $client->id, orderedAt: CarbonImmutable::now(),
        products: [new OrderLine($product->id, 1)],
    ));

    $response = $this->deleteJson("/api/v1/clients/{$client->id}");
    $response->assertStatus(409);
    $response->assertJsonPath('error', 'client_has_orders');
});
