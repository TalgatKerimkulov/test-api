<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Batch;
use App\Models\BatchItem;
use App\Models\Product;
use App\Models\Storage;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<BatchItem>
 */
class BatchItemFactory extends Factory
{
    protected $model = BatchItem::class;

    public function definition(): array
    {
        return [
            'batch_id' => Batch::factory(),
            'product_id' => Product::factory(),
            'storage_id' => Storage::factory(),
            'qty_purchased' => fake()->numberBetween(5, 100),
            'qty_refunded_to_provider' => 0,
            'qty_sold' => 0,
            'qty_returned_by_clients' => 0,
            'purchase_price' => fake()->randomFloat(2, 5, 100),
            'sale_price' => fake()->randomFloat(2, 10, 200),
        ];
    }
}
