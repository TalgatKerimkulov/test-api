<?php

declare(strict_types=1);

namespace App\Actions\Product\Api;

use App\Models\Product;

class ProductUpdateAction
{
    public function handle(ProductUpsertActionData $input): Product
    {
        $product = $input->product;
        if (! $product instanceof Product) {
            throw new \InvalidArgumentException('Product is required for update.');
        }

        $product->update($input->attributes);

        if ($input->variations !== null) {
            $this->syncVariations($product, $input->variations);
        }

        return $product->fresh('variations');
    }

    /**
     * @param array<int, array<string, mixed>> $variations
     */
    private function syncVariations(Product $product, array $variations): void
    {
        if ($variations === []) {
            return;
        }

        $incomingIds = [];
        foreach ($variations as $variation) {
            $model = $product->variations()->updateOrCreate(
                ['id' => $variation['id'] ?? null],
                [
                    'sku' => $variation['sku'],
                    'name' => $variation['name'],
                    'sale_price' => $variation['sale_price'] ?? null,
                    'attributes' => $variation['attributes'] ?? null,
                    'is_active' => $variation['is_active'] ?? true,
                ],
            );
            $incomingIds[] = $model->id;
        }

        $product->variations()->whereNotIn('id', $incomingIds)->delete();
    }
}

