<?php

declare(strict_types=1);

namespace App\Actions\Product\Admin;

use App\Exceptions\ApiException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ProductCreateActionData
{
    public function __construct(
        public readonly int $categoryId,
        public readonly string $name,
        public readonly string $sku,
        public readonly ?string $salePrice,
    ) {
    }

    public static function fromRequest(Request $request): self
    {
        $validator = Validator::make($request->all(), [
            'category_id' => ['required', 'integer', 'exists:categories,id'],
            'name' => ['required', 'string', 'max:255'],
            'sku' => ['required', 'string', 'max:64', 'unique:products,sku'],
            'sale_price' => ['nullable', 'numeric', 'min:0'],
        ]);

        if ($validator->fails()) {
            throw new ApiException($validator->errors()->first(), 422);
        }

        return new self(
            categoryId: (int) $request->integer('category_id'),
            name: (string) $request->string('name'),
            sku: (string) $request->string('sku'),
            salePrice: $request->filled('sale_price') ? (string) $request->input('sale_price') : null,
        );
    }
}
