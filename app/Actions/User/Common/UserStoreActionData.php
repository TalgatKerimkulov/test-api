<?php

declare(strict_types=1);

namespace App\Actions\User\Common;

use App\Enums\Role;
use App\Exceptions\ApiException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class UserStoreActionData
{
    /** @param array<string,mixed> $validated */
    public function __construct(public readonly array $validated) {}

    public static function fromRequest(Request $request): self
    {
        $validator = Validator::make($request->all(), [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', Password::min(6)],
            'phone' => ['nullable', 'string', 'max:32'],
            'role' => ['required', 'string', Rule::in(Role::values())],
        ]);
        if ($validator->fails()) {
            throw new ApiException($validator->errors()->first(), 422);
        }
        return new self($validator->validated());
    }
}

