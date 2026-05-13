<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Enums\UserType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        // backwards-compatible alias: accept legacy client_id field
        if ($this->has('client_id') && ! $this->has('user_id')) {
            $this->merge(['user_id' => $this->input('client_id')]);
        }
    }

    public function rules(): array
    {
        return [
            'user_id' => [
                'required', 'integer',
                Rule::exists('users', 'id')
                    ->where(fn ($q) => $q->where('type', UserType::Client->value)
                        ->whereNull('deleted_at')),
            ],
            'ordered_at' => ['nullable', 'date'],
            'products' => ['required', 'array', 'min:1'],
            'products.*.id' => ['required', 'integer', 'exists:products,id', 'distinct'],
            'products.*.qty' => ['required', 'integer', 'min:1'],
        ];
    }
}
