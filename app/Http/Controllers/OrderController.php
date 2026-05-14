<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\Client\ClientOrderCreateAction;
use App\Actions\Client\ClientOrderCreateActionData;
use App\Http\Resources\OrderResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OrderController
{
    public function store(Request $request, ClientOrderCreateAction $action): JsonResponse
    {
        $order = $action->handle(ClientOrderCreateActionData::fromRequest($request));

        return (new OrderResource($order))->response()->setStatusCode(201);
    }
}
