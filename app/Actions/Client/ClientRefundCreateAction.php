<?php

declare(strict_types=1);

namespace App\Actions\Client;

use App\Models\ClientRefund;
use App\Services\ClientRefundService;

class ClientRefundCreateAction
{
    public function __construct(private readonly ClientRefundService $service)
    {
    }

    public function handle(ClientRefundCreateActionData $input): ClientRefund
    {
        return $this->service->create($input->payload);
    }
}

