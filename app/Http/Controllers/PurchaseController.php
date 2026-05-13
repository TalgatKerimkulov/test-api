<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\DTO\PurchasePayload;
use App\Http\Requests\StorePurchaseRequest;
use App\Http\Resources\BatchResource;
use App\Services\PurchaseService;
use Illuminate\Http\JsonResponse;

class PurchaseController
{
    public function store(StorePurchaseRequest $request, PurchaseService $service): JsonResponse
    {
        $batch = $service->create(PurchasePayload::fromRequest($request));

        return (new BatchResource($batch))->response()->setStatusCode(201);
    }
}
