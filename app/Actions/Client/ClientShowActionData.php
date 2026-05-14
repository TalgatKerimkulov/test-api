<?php

declare(strict_types=1);

namespace App\Actions\Client;

use App\Exceptions\ApiException;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ClientShowActionData
{
    public function __construct(public readonly User $client)
    {
    }

    public static function fromRequest(Request $request): self
    {
        $client = $request->route('client');
        if (! $client) {
            $validator = Validator::make($request->all(), [
                'id' => ['required', 'integer', 'exists:users,id'],
            ]);
            if ($validator->fails()) {
                throw new ApiException($validator->errors()->first(), 422);
            }
            $client = $request->input('id');
        }

        if (is_numeric($client)) {
            $client = User::query()->find((int) $client);
        }

        if (! $client instanceof User) {
            throw new ApiException('Client not found.', 404);
        }

        return new self($client);
    }
}
