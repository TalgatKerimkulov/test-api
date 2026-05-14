<?php

declare(strict_types=1);

namespace App\Actions\Client;

use App\Exceptions\ApiException;
use App\Models\User;

class ClientShowAction
{
    public function handle(ClientShowActionData $input): User
    {
        if (! $input->client->isClient()) {
            throw new ApiException('Client not found.', 404);
        }

        return $input->client;
    }
}

