<?php

declare(strict_types=1);

use App\Models\Category;
use App\Models\Product;
use App\Models\Provider;

it('admin can create root category', function () {
    actingAsAdmin();
    $provider = Provider::factory()->create();

    $response = $this->postJson('/api/v1/categories', [
        'provider_id' => $provider->id,
        'parent_id' => null,
        'name' => 'Ahmad Tea',
    ]);

    $response->assertCreated();
    $response->assertJsonPath('data.parent_id', null);
});

it('admin can create child category', function () {
    actingAsAdmin();
    $root = Category::factory()->create();

    $response = $this->postJson('/api/v1/categories', [
        'provider_id' => $root->provider_id,
        'parent_id' => $root->id,
        'name' => 'Black Tea',
    ]);

    $response->assertCreated();
    $response->assertJsonPath('data.parent_id', $root->id);
});

it('cannot delete category with products', function () {
    actingAsAdmin();
    $category = Category::factory()->create();
    Product::factory()->create(['category_id' => $category->id]);

    $response = $this->deleteJson("/api/v1/categories/{$category->id}");
    $response->assertStatus(409);
});
