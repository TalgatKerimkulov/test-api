<?php

declare(strict_types=1);

namespace App\Actions\Category\Admin;

use App\Exceptions\ApiException;
use App\Models\Category;
use Illuminate\Support\Facades\DB;

class CategoryDeleteAction
{
    public function handle(CategoryDeleteActionData $input): bool
    {
        $category = Category::query()->find($input->id);
        if (! $category) {
            throw new ApiException('Category not found', 404);
        }

        if ($category->children()->exists() || $category->products()->exists()) {
            throw new ApiException('Category has child categories or products and cannot be deleted.', 409);
        }

        DB::transaction(function () use ($category): void {
            $category->delete();
        });

        return true;
    }
}
