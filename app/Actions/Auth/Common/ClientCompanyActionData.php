<?php

declare(strict_types=1);

namespace App\Actions\Auth\Common;

use App\Exceptions\ApiException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class ClientCompanyActionData
{
    /** @param array<string,mixed> $validated */
    public function __construct(public readonly array $validated)
    {
    }

    public static function fromRequest(Request $request): self
    {
        $validator = Validator::make($request->all(), [
            'name' => ['required', 'string', 'max:255'],
            'inn' => ['nullable', 'string', 'max:32', Rule::unique('providers', 'inn')->whereNull('deleted_at')],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:32'],
        ]);
        if ($validator->fails()) {
            throw new ApiException($validator->errors()->first(), 422);
        }

        return new self($validator->validated());
    }
}

