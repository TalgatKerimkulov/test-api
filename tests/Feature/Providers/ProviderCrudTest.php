<?php

declare(strict_types=1);

use App\Enums\Role;
use App\Models\Category;
use App\Models\Provider;

it('warehouse_manager can create a provider', function () {
    actingAsRole(Role::WarehouseManager->value);

    $response = $this->postJson('/api/v1/providers', [
        'name' => 'Ahmad Tea',
        'phone' => '+998901234567',
        'email' => 'provider@example.com',
    ]);

    $response->assertCreated();
    $this->assertDatabaseHas('providers', ['name' => 'Ahmad Tea']);
});

it('manager cannot create a provider', function () {
    actingAsRole(Role::Manager->value);

    $response = $this->postJson('/api/v1/providers', ['name' => 'X']);
    $response->assertStatus(403);
});

it('cannot delete provider when it has related categories or batches', function () {
    actingAsAdmin();
    $provider = Provider::factory()->create();
    Category::factory()->create(['provider_id' => $provider->id]);

    $response = $this->deleteJson("/api/v1/providers/{$provider->id}");
    $response->assertStatus(409);
    $response->assertJsonPath('error', 'provider_has_relations');
});

it('admin can delete a provider with no relations', function () {
    actingAsAdmin();
    $provider = Provider::factory()->create();

    $response = $this->deleteJson("/api/v1/providers/{$provider->id}");
    $response->assertOk();
    $this->assertSoftDeleted('providers', ['id' => $provider->id]);
});
