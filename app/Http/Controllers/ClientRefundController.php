<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\DTO\ClientRefundPayload;
use App\Http\Requests\StoreClientRefundRequest;
use App\Http\Resources\ClientRefundResource;
use App\Services\ClientRefundService;
use Illuminate\Http\JsonResponse;

class ClientRefundController
{
    public function store(StoreClientRefundRequest $request, ClientRefundService $service): JsonResponse
    {
        $refund = $service->create(ClientRefundPayload::fromRequest($request));

        return (new ClientRefundResource($refund))->response()->setStatusCode(201);
    }
}
