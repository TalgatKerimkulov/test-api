<?php

declare(strict_types=1);

use App\DTO\PurchaseLine;
use App\DTO\PurchasePayload;
use App\Models\Category;
use App\Models\Product;
use App\Models\Provider;
use App\Models\Storage;
use App\Services\PurchaseService;
use Carbon\CarbonImmutable;

it('admin can create a product', function () {
    actingAsAdmin();
    $category = Category::factory()->create();

    $response = $this->postJson('/api/v1/products', [
        'category_id' => $category->id,
        'name' => 'Ahmad Tea Earl Grey, 500g',
        'sku' => 'AHMAD-EARL-500',
        'sale_price' => 13000,
    ]);

    $response->assertCreated();
    $this->assertDatabaseHas('products', ['sku' => 'AHMAD-EARL-500']);
});

it('filters products by category', function () {
    actingAsAdmin();
    $cat1 = Category::factory()->create();
    $cat2 = Category::factory()->create();
    Product::factory()->create(['category_id' => $cat1->id]);
    Product::factory()->count(2)->create(['category_id' => $cat2->id]);

    $response = $this->getJson('/api/v1/products?filter[category_id]='.$cat2->id);

    $response->assertOk();
    expect($response->json('data'))->toHaveCount(2);
});

it('cannot delete product that participated in purchases', function () {
    actingAsAdmin();
    $provider = Provider::factory()->create();
    $storage = Storage::factory()->create();
    $product = Product::factory()->create();
    app(PurchaseService::class)->create(new PurchasePayload(
        providerId: $provider->id, storageId: $storage->id,
        purchasedAt: CarbonImmutable::now(),
        items: [new PurchaseLine($product->id, 3, '5.00', '10.00')],
    ));

    $response = $this->deleteJson("/api/v1/products/{$product->id}");
    $response->assertStatus(409);
});
