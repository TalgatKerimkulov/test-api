<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\BatchProfitRequest;
use App\Services\BatchProfitService;
use Illuminate\Http\JsonResponse;

class BatchProfitController
{
    public function index(BatchProfitRequest $request, BatchProfitService $service): JsonResponse
    {
        $rows = $service->calculate(
            from: $request->input('from'),
            to: $request->input('to'),
            batchId: $request->filled('batch_id') ? (int) $request->input('batch_id') : null,
            providerId: $request->filled('provider_id') ? (int) $request->input('provider_id') : null,
        );

        return response()->json([
            'data' => $rows->map(fn ($r) => [
                'batch_id' => (int) $r->batch_id,
                'code' => $r->code,
                'status' => $r->status,
                'provider_name' => $r->provider_name,
                'purchased_at' => $r->purchased_at,
                'purchase_cost_for_sold' => (string) $r->purchase_cost_for_sold,
                'gross_sales' => (string) $r->gross_sales,
                'client_refund_loss' => (string) $r->client_refund_loss,
                'profit' => (string) $r->profit,
            ])->all(),
        ]);
    }
}
