<?php

declare(strict_types=1);

namespace App\Actions\Category\Admin;

use App\Exceptions\ApiException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CategoryCreateActionData
{
    public function __construct(
        public readonly ?int $providerId,
        public readonly ?int $parentId,
        public readonly string $name,
        public readonly ?string $slug,
    ) {
    }

    public static function fromRequest(Request $request): self
    {
        $validator = Validator::make($request->all(), [
            'provider_id' => ['nullable', 'integer', 'exists:providers,id'],
            'parent_id' => ['nullable', 'integer', 'exists:categories,id'],
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', 'unique:categories,slug'],
        ]);

        if ($validator->fails()) {
            throw new ApiException($validator->errors()->first(), 422);
        }

        return new self(
            providerId: $request->integer('provider_id') ?: null,
            parentId: $request->integer('parent_id') ?: null,
            name: (string) $request->string('name'),
            slug: $request->filled('slug') ? (string) $request->string('slug') : null,
        );
    }
}
