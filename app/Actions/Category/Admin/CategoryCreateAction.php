<?php

declare(strict_types=1);

namespace App\Actions\Category\Admin;

use App\Models\Category;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CategoryCreateAction
{
    public function handle(CategoryCreateActionData $input): array
    {
        $category = DB::transaction(function () use ($input): Category {
            return Category::query()->create([
                'provider_id' => $input->providerId,
                'parent_id' => $input->parentId,
                'name' => $input->name,
                'slug' => $input->slug ?? $this->uniqueSlug($input->name),
            ]);
        });

        return $category->toArray();
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
