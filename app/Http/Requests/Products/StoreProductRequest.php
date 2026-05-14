<?php

declare(strict_types=1);

namespace App\Http\Requests\Products;

use Illuminate\Foundation\Http\FormRequest;

class StoreProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'category_id' => ['required', 'integer', 'exists:categories,id'],
            'name' => ['required', 'string', 'max:255'],
            'sku' => ['required', 'string', 'max:64', 'unique:products,sku'],
            'sale_price' => ['nullable', 'numeric', 'min:0'],
            'variations' => ['nullable', 'array'],
            'variations.*.sku' => ['required_with:variations', 'string', 'max:64', 'distinct', 'unique:product_variations,sku'],
            'variations.*.name' => ['required_with:variations', 'string', 'max:255'],
            'variations.*.sale_price' => ['nullable', 'numeric', 'min:0'],
            'variations.*.attributes' => ['nullable', 'array'],
            'variations.*.is_active' => ['nullable', 'boolean'],
        ];
    }
}
