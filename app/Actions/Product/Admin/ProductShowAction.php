<?php

declare(strict_types=1);

namespace App\Actions\Product\Admin;

use App\Exceptions\ApiException;
use App\Models\Product;

class ProductShowAction
{
    public function handle(ProductShowActionData $input): array
    {
        $product = Product::query()->find($input->id);
        if (! $product) {
            throw new ApiException('Product not found', 404);
        }

        return $product->toArray();
    }
}
