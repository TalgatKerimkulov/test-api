<?php

declare(strict_types=1);

namespace App\Actions\Client;

use App\Exceptions\ApiException;
use App\Exceptions\RelationConflictException;

class ClientDeleteAction
{
    public function handle(ClientDeleteActionData $input): void
    {
        if (! $input->client->isClient()) {
            throw new ApiException('Client not found.', 404);
        }

        if ($input->client->orders()->exists()) {
            throw new RelationConflictException(
                'client_has_orders',
                'Client has orders and cannot be deleted.',
            );
        }

        $input->client->delete();
    }
}

