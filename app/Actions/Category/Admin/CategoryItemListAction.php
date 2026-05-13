<?php

declare(strict_types=1);

namespace App\Actions\Category\Admin;

use App\Models\Category;

class CategoryItemListAction
{
    /**
     * @return array<int, array{id:int,name:string}>
     */
    public function handle(CategoryItemListActionData $input): array
    {
        return Category::query()
            ->when($input->search, fn ($query, $search) => $query->where('name', 'ilike', "%{$search}%"))
            ->orderBy('name')
            ->limit(100)
            ->get(['id', 'name'])
            ->map(fn (Category $category): array => [
                'id' => $category->id,
                'name' => $category->name,
            ])
            ->values()
            ->all();
    }
}
