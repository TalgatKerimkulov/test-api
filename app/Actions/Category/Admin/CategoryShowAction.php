<?php

declare(strict_types=1);

namespace App\Actions\Category\Admin;

use App\Exceptions\ApiException;
use App\Models\Category;

class CategoryShowAction
{
    public function handle(CategoryShowActionData $input): array
    {
        $category = Category::query()->with('children')->find($input->id);
        if (! $category) {
            throw new ApiException('Category not found', 404);
        }

        return $category->toArray();
    }
}
