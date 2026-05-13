<?php

declare(strict_types=1);

namespace App\Actions\Product\Admin;

use App\Models\Product;

class ProductIndexAction
{
    /**
     * @return array<string, mixed>
     */
    public function handle(ProductIndexActionData $input): array
    {
        $query = Product::query()
            ->when($input->categoryId, fn ($q, $v) => $q->where('category_id', $v))
            ->when($input->providerId, fn ($q, $v) => $q->whereHas('category', fn ($c) => $c->where('provider_id', $v)))
            ->when($input->name, fn ($q, $v) => $q->where('name', 'ilike', "%{$v}%"))
            ->orderBy('id');

        $total = (clone $query)->count();
        $items = $query
            ->offset(($input->page - 1) * $input->limit)
            ->limit($input->limit)
            ->get()
            ->toArray();
        $lastPage = (int) max(1, (int) ceil($total / $input->limit));

        return [
            'pagination' => [
                'total' => $total,
                'limit' => $input->limit,
                'current_page' => $input->page,
                'last_page' => $lastPage,
                'next_page' => $input->page < $lastPage ? $input->page + 1 : null,
                'prev_page' => $input->page > 1 ? $input->page - 1 : null,
            ],
            'items' => $items,
        ];
    }
}
