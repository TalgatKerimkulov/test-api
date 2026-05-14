<?php

declare(strict_types=1);

namespace App\Actions\Client;

use App\Exceptions\ApiException;
use App\Models\User;
use Illuminate\Http\Request;

class ClientShowActionData
{
    public function __construct(public readonly User $client)
    {
    }

    public static function fromRequest(Request $request): self
    {
        $client = $request->route('client');
        if (! $client instanceof User) {
            throw new ApiException('Client not found.', 404);
        }

        return new self($client);
    }
}

