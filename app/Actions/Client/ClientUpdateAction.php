<?php

declare(strict_types=1);

namespace App\Actions\Client;

use App\Exceptions\ApiException;
use App\Models\User;

class ClientUpdateAction
{
    public function handle(ClientUpdateActionData $input): User
    {
        if (! $input->client->isClient()) {
            throw new ApiException('Client not found.', 404);
        }

        $input->client->update($input->attributes);

        return $input->client->fresh();
    }
}

