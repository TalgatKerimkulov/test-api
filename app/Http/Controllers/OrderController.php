<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\DTO\OrderPayload;
use App\Http\Requests\StoreOrderRequest;
use App\Http\Resources\OrderResource;
use App\Services\OrderService;
use Illuminate\Http\JsonResponse;

class OrderController
{
    public function store(StoreOrderRequest $request, OrderService $service): JsonResponse
    {
        $order = $service->create(OrderPayload::fromRequest($request));

        return (new OrderResource($order))->response()->setStatusCode(201);
    }
}
