<?php

declare(strict_types=1);

namespace App\Actions\User\Common;

use App\Enums\Role;
use App\Exceptions\ApiException;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class UserUpdateActionData
{
    /** @param array<string,mixed> $validated */
    public function __construct(public readonly array $validated, public readonly User $user) {}

    public static function fromRequest(Request $request, User $user): self
    {
        $id = $user->id;
        $validator = Validator::make($request->all(), [
            'name' => ['sometimes', 'string', 'max:255'],
            'email' => ['sometimes', 'email', 'max:255', Rule::unique('users', 'email')->ignore($id)->whereNull('deleted_at')],
            'password' => ['sometimes', 'string', Password::min(6)],
            'phone' => ['sometimes', 'nullable', 'string', 'max:32'],
            'role' => ['sometimes', 'string', Rule::in(Role::values())],
        ]);
        if ($validator->fails()) {
            throw new ApiException($validator->errors()->first(), 422);
        }
        return new self($validator->validated(), $user);
    }
}

