<?php

declare(strict_types=1);

namespace App\Http\Requests\Providers;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProviderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $id = $this->route('provider')?->id ?? $this->route('provider');

        return [
            'name' => ['sometimes', 'string', 'max:255'],
            'inn' => ['sometimes', 'nullable', 'string', 'max:32',
                Rule::unique('providers', 'inn')->ignore($id)->whereNull('deleted_at')],
            'email' => ['sometimes', 'nullable', 'email', 'max:255'],
            'phone' => ['sometimes', 'nullable', 'string', 'max:32'],
        ];
    }
}
