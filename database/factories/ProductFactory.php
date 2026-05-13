<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Product>
 */
class ProductFactory extends Factory
{
    protected $model = Product::class;

    public function definition(): array
    {
        return [
            'category_id' => Category::factory(),
            'sku' => Str::upper(Str::random(8)),
            'name' => fake()->unique()->words(2, true),
            'default_sale_price' => fake()->randomFloat(2, 10, 500),
        ];
    }
}
