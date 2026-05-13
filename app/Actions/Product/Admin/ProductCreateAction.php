<?php

declare(strict_types=1);

namespace App\Actions\Product\Admin;

use App\Models\Product;
use Illuminate\Support\Facades\DB;

class ProductCreateAction
{
    public function handle(ProductCreateActionData $input): array
    {
        $product = DB::transaction(function () use ($input): Product {
            return Product::query()->create([
                'category_id' => $input->categoryId,
                'name' => $input->name,
                'sku' => $input->sku,
                'default_sale_price' => $input->salePrice,
            ]);
        });

        return $product->toArray();
    }
}
