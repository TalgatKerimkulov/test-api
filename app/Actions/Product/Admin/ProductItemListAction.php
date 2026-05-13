<?php

declare(strict_types=1);

namespace App\Actions\Product\Admin;

use App\Models\Product;

class ProductItemListAction
{
    /**
     * @return array<int, array{id:int,name:string}>
     */
    public function handle(ProductItemListActionData $input): array
    {
        return Product::query()
            ->when($input->search, fn ($q, $s) => $q->where('name', 'ilike', "%{$s}%"))
            ->orderBy('name')
            ->limit(100)
            ->get(['id', 'name'])
            ->map(fn (Product $product): array => [
                'id' => $product->id,
                'name' => $product->name,
            ])
            ->values()
            ->all();
    }
}
