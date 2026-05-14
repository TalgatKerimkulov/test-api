<?php

declare(strict_types=1);

namespace App\Actions\Auth\Common;

use App\Exceptions\ApiException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class LoginActionData
{
    /** @param array<string,mixed> $validated */
    public function __construct(public readonly array $validated)
    {
    }

    public static function fromRequest(Request $request): self
    {
        $validator = Validator::make($request->all(), [
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);
        if ($validator->fails()) {
            throw new ApiException($validator->errors()->first(), 422);
        }

        return new self($validator->validated());
    }
}

