<?php

declare(strict_types=1);

namespace App\Actions\Provider\Admin;

use App\Exceptions\ApiException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class ProviderCreateActionData
{
    public function __construct(
        public readonly string $name,
        public readonly ?string $inn,
        public readonly ?string $email,
        public readonly ?string $phone,
    ) {
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

        return new self(
            name: (string) $request->string('name'),
            inn: $request->filled('inn') ? (string) $request->string('inn') : null,
            email: $request->filled('email') ? (string) $request->string('email') : null,
            phone: $request->filled('phone') ? (string) $request->string('phone') : null,
        );
    }
}
