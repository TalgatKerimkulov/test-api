<?php

declare(strict_types=1);

namespace App\Actions\Category\Common;

use App\Exceptions\ApiException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CategoryStoreActionData
{
    /** @param array<string,mixed> $validated */
    public function __construct(public readonly array $validated) {}

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
        return new self($validator->validated());
    }
}

