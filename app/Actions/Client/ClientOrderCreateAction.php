<?php

declare(strict_types=1);

namespace App\Actions\Client;

use App\Models\Order;
use App\Services\OrderService;

class ClientOrderCreateAction
{
    public function __construct(private readonly OrderService $service)
    {
    }

    public function handle(ClientOrderCreateActionData $input): Order
    {
        return $this->service->create($input->payload);
    }
}

