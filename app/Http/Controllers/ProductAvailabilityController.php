<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Services\AvailableProductService;
use Illuminate\Http\JsonResponse;

class ProductAvailabilityController
{
    public function index(AvailableProductService $service): JsonResponse
    {
        return response()->json([
            'data' => $service->list()->map(fn ($row) => [
                'id' => (int) $row->id,
                'name' => $row->name,
                'category_name' => $row->category_name,
                'price' => $row->price !== null ? (string) $row->price : null,
                'qty' => (int) $row->qty,
            ])->all(),
        ]);
    }
}
