<?php

declare(strict_types=1);

namespace App\Actions\Product\Admin;

use App\Exceptions\ApiException;
use App\Models\Product;
use Illuminate\Support\Facades\DB;

class ProductUpdateAction
{
    public function handle(ProductUpdateActionData $input): array
    {
        $product = Product::query()->find($input->id);
        if (! $product) {
            throw new ApiException('Product not found', 404);
        }

        $payload = $input->payload;
        if (array_key_exists('sale_price', $payload)) {
            $payload['default_sale_price'] = $payload['sale_price'];
            unset($payload['sale_price']);
        }

        DB::transaction(function () use ($product, $payload): void {
            $product->update($payload);
        });

        return $product->fresh()->toArray();
    }
}
