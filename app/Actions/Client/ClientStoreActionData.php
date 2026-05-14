<?php

declare(strict_types=1);

namespace App\Actions\Client;

use App\Exceptions\ApiException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class ClientStoreActionData
{
    public function __construct(
        public readonly string $name,
        public readonly ?string $phone,
        public readonly ?string $email,
        public readonly ?string $address,
    ) {
    }

    public static function fromRequest(Request $request): self
    {
        $validator = Validator::make($request->all(), [
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:32'],
            'email' => ['nullable', 'email', 'max:255', Rule::unique('users', 'email')->whereNull('deleted_at')],
            'address' => ['nullable', 'string'],
        ]);

        if ($validator->fails()) {
            throw new ApiException($validator->errors()->first(), 422);
        }

        return new self(
            name: (string) $request->string('name'),
            phone: $request->filled('phone') ? (string) $request->string('phone') : null,
            email: $request->filled('email') ? (string) $request->string('email') : null,
            address: $request->filled('address') ? (string) $request->string('address') : null,
        );
    }
}

