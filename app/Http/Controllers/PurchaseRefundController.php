<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\DTO\PurchaseRefundPayload;
use App\Http\Requests\StorePurchaseRefundRequest;
use App\Http\Resources\PurchaseRefundResource;
use App\Services\PurchaseRefundService;
use Illuminate\Http\JsonResponse;

class PurchaseRefundController
{
    public function store(StorePurchaseRefundRequest $request, PurchaseRefundService $service): JsonResponse
    {
        $refund = $service->create(PurchaseRefundPayload::fromRequest($request));

        return (new PurchaseRefundResource($refund))->response()->setStatusCode(201);
    }
}
