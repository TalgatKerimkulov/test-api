<?php

declare(strict_types=1);

namespace App\Actions\Category\Common;

use App\Exceptions\RelationConflictException;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class CategoryAction
{
    public function index(Request $request): Collection
    {
        $query = Category::query()->orderBy('id');
        if ($request->boolean('tree', true)) {
            $query->whereNull('parent_id')->with('children.children.children');
        }

        return $query->get();
    }

    /** @param array<string,mixed> $data */
    public function store(array $data): Category
    {
        $data['slug'] = $data['slug'] ?? $this->uniqueSlug((string) $data['name']);

        return Category::query()->create($data);
    }

    public function show(Category $category): Category
    {
        return $category->load('children');
    }

    /** @param array<string,mixed> $data */
    public function update(Category $category, array $data): Category
    {
        $category->update($data);

        return $category->fresh();
    }

    public function destroy(Category $category): void
    {
        if ($category->children()->exists() || $category->products()->exists()) {
            throw new RelationConflictException(
                'category_has_children_or_products',
                'Category has child categories or products and cannot be deleted.',
            );
        }
        $category->delete();
    }

    private function uniqueSlug(string $name): string
    {
        $base = Str::slug($name);
        $slug = $base;
        $i = 2;
        while (Category::query()->where('slug', $slug)->exists()) {
            $slug = $base.'-'.$i++;
        }

        return $slug;
    }
}

