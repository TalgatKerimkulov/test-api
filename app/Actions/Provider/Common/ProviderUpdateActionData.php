<?php

declare(strict_types=1);

namespace App\Actions\Provider\Common;

use App\Exceptions\ApiException;
use App\Models\Provider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class ProviderUpdateActionData
{
    /** @param array<string,mixed> $validated */
    public function __construct(public readonly array $validated, public readonly Provider $provider) {}

    public static function fromRequest(Request $request, Provider $provider): self
    {
        $id = $provider->id;
        $validator = Validator::make($request->all(), [
            'name' => ['sometimes', 'string', 'max:255'],
            'inn' => ['sometimes', 'nullable', 'string', 'max:32',
                Rule::unique('providers', 'inn')->ignore($id)->whereNull('deleted_at')],
            'email' => ['sometimes', 'nullable', 'email', 'max:255'],
            'phone' => ['sometimes', 'nullable', 'string', 'max:32'],
        ]);
        if ($validator->fails()) {
            throw new ApiException($validator->errors()->first(), 422);
        }
        return new self($validator->validated(), $provider);
    }
}

