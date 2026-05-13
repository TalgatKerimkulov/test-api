<?php

declare(strict_types=1);

namespace App\Actions\Category\Admin;

use App\Exceptions\ApiException;
use App\Models\Category;
use Illuminate\Support\Facades\DB;

class CategoryUpdateAction
{
    public function handle(CategoryUpdateActionData $input): array
    {
        $category = Category::query()->find($input->id);
        if (! $category) {
            throw new ApiException('Category not found', 404);
        }

        DB::transaction(function () use ($category, $input): void {
            $category->update($input->payload);
        });

        return $category->fresh()->toArray();
    }
}
