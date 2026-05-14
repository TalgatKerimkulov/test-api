<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\Client\ClientRefundCreateAction;
use App\Actions\Client\ClientRefundCreateActionData;
use App\Http\Resources\ClientRefundResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ClientRefundController
{
    public function store(Request $request, ClientRefundCreateAction $action): JsonResponse
    {
        $refund = $action->handle(ClientRefundCreateActionData::fromRequest($request));

        return (new ClientRefundResource($refund))->response()->setStatusCode(201);
    }
}
