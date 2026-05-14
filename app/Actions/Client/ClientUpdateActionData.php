<?php

declare(strict_types=1);

namespace App\Actions\Client;

use App\Exceptions\ApiException;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class ClientUpdateActionData
{
    /**
     * @param array<string, mixed> $attributes
     */
    public function __construct(
        public readonly User $client,
        public readonly array $attributes,
    ) {
    }

    public static function fromRequest(Request $request): self
    {
        $client = $request->route('client');
        if (! $client) {
            $idValidator = Validator::make($request->all(), [
                'id' => ['required', 'integer', 'exists:users,id'],
            ]);
            if ($idValidator->fails()) {
                throw new ApiException($idValidator->errors()->first(), 422);
            }
            $client = $request->input('id');
        }

        if (is_numeric($client)) {
            $client = User::query()->find((int) $client);
        }

        if (! $client instanceof User) {
            throw new ApiException('Client not found.', 404);
        }

        $validator = Validator::make($request->all(), [
            'name' => ['sometimes', 'string', 'max:255'],
            'phone' => ['sometimes', 'nullable', 'string', 'max:32'],
            'email' => ['sometimes', 'nullable', 'email', 'max:255',
                Rule::unique('users', 'email')->ignore($client->id)->whereNull('deleted_at')],
            'address' => ['sometimes', 'nullable', 'string'],
        ]);

        if ($validator->fails()) {
            throw new ApiException($validator->errors()->first(), 422);
        }

        /** @var array<string, mixed> $attributes */
        $attributes = $validator->validated();

        return new self($client, $attributes);
    }
}
