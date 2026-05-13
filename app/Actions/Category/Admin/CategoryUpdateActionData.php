<?php

declare(strict_types=1);

namespace App\Actions\Category\Admin;

use App\Exceptions\ApiException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class CategoryUpdateActionData
{
    /**
     * @param array<string, mixed> $payload
     */
    public function __construct(
        public readonly int $id,
        public readonly array $payload,
    ) {
    }

    public static function fromRequest(Request $request): self
    {
        $id = (int) ($request->input('id') ?? 0);
        if ($id <= 0) {
            throw new ApiException('Category id is required', 422);
        }

        $validator = Validator::make($request->all(), [
            'id' => ['required', 'integer', 'exists:categories,id'],
            'provider_id' => ['sometimes', 'nullable', 'integer', 'exists:providers,id'],
            'parent_id' => ['sometimes', 'nullable', 'integer', 'exists:categories,id', Rule::notIn([$id])],
            'name' => ['sometimes', 'string', 'max:255'],
            'slug' => ['sometimes', 'nullable', 'string', 'max:255', Rule::unique('categories', 'slug')->ignore($id)],
        ]);

        if ($validator->fails()) {
            throw new ApiException($validator->errors()->first(), 422);
        }

        $data = $validator->validated();
        unset($data['id']);

        return new self(id: $id, payload: $data);
    }
}
