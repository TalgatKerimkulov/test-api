<?php

declare(strict_types=1);

namespace App\Actions\Product\Api;

use App\Models\Product;

class ProductUpsertActionData
{
    /**
     * @param array<string, mixed> $attributes
     * @param array<int, array<string, mixed>>|null $variations
     */
    public function __construct(
        public readonly array $attributes,
        public readonly ?array $variations,
        public readonly ?Product $product = null,
    ) {
    }

    /**
     * @param array<string, mixed> $validated
     * @param array<int, array<string, mixed>>|null $variations
     */
    public static function fromValidated(array $validated, ?array $variations = null, ?Product $product = null): self
    {
        $attributes = $validated;
        if (array_key_exists('sale_price', $attributes)) {
            $attributes['default_sale_price'] = $attributes['sale_price'];
            unset($attributes['sale_price']);
        }

        return new self(
            attributes: $attributes,
            variations: $variations,
            product: $product,
        );
    }
}

