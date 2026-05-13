<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\RemainingQuantitiesRequest;
use App\Services\StockService;
use Carbon\CarbonImmutable;
use Illuminate\Http\JsonResponse;

class StorageReportController
{
    public function remaining(RemainingQuantitiesRequest $request, StockService $service): JsonResponse
    {
        $rows = $service->remainingOnDate(
            CarbonImmutable::parse($request->input('date')),
            $request->filled('storage_id') ? (int) $request->input('storage_id') : null,
        );

        return response()->json([
            'data' => $rows->map(fn ($r) => [
                'storage_id' => (int) $r->storage_id,
                'storage_name' => $r->storage_name,
                'product_id' => (int) $r->product_id,
                'product_name' => $r->product_name,
                'qty' => (int) $r->qty,
            ])->all(),
        ]);
    }
}
