<?php

declare(strict_types=1);

namespace App\Actions\Product\Admin;

use App\Exceptions\ApiException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class ProductUpdateActionData
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
            throw new ApiException('Product id is required', 422);
        }

        $validator = Validator::make($request->all(), [
            'id' => ['required', 'integer', 'exists:products,id'],
            'category_id' => ['sometimes', 'integer', 'exists:categories,id'],
            'name' => ['sometimes', 'string', 'max:255'],
            'sku' => ['sometimes', 'string', 'max:64', Rule::unique('products', 'sku')->ignore($id)->whereNull('deleted_at')],
            'sale_price' => ['sometimes', 'nullable', 'numeric', 'min:0'],
        ]);

        if ($validator->fails()) {
            throw new ApiException($validator->errors()->first(), 422);
        }

        $data = $validator->validated();
        unset($data['id']);

        return new self($id, $data);
    }
}
