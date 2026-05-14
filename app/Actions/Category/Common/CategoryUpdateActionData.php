<?php

declare(strict_types=1);

namespace App\Actions\Category\Common;

use App\Exceptions\ApiException;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class CategoryUpdateActionData
{
    /** @param array<string,mixed> $validated */
    public function __construct(public readonly array $validated, public readonly Category $category) {}

    public static function fromRequest(Request $request, Category $category): self
    {
        $id = $category->id;
        $validator = Validator::make($request->all(), [
            'provider_id' => ['sometimes', 'nullable', 'integer', 'exists:providers,id'],
            'parent_id' => ['sometimes', 'nullable', 'integer', 'exists:categories,id', Rule::notIn([$id])],
            'name' => ['sometimes', 'string', 'max:255'],
            'slug' => ['sometimes', 'nullable', 'string', 'max:255', Rule::unique('categories', 'slug')->ignore($id)],
        ]);
        if ($validator->fails()) {
            throw new ApiException($validator->errors()->first(), 422);
        }
        return new self($validator->validated(), $category);
    }
}

