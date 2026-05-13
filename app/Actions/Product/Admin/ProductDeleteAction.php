<?php

declare(strict_types=1);

namespace App\Actions\Product\Admin;

use App\Exceptions\ApiException;
use App\Models\Product;
use Illuminate\Support\Facades\DB;

class ProductDeleteAction
{
    public function handle(ProductDeleteActionData $input): bool
    {
        $product = Product::query()->find($input->id);
        if (! $product) {
            throw new ApiException('Product not found', 404);
        }

        if ($product->batchItems()->exists()) {
            throw new ApiException('Product participates in purchases/orders and cannot be deleted.', 409);
        }

        DB::transaction(function () use ($product): void {
            $product->delete();
        });

        return true;
    }
}
