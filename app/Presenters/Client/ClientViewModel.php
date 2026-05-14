<?php

declare(strict_types=1);

namespace App\Presenters\Client;

use App\Http\Resources\ClientResource;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Pagination\LengthAwarePaginator;

class ClientViewModel
{
    public static function presentCollection(LengthAwarePaginator $clients): JsonResponse
    {
        return ClientResource::collection($clients)->response();
    }

    public static function presentItem(User $client, int $status = 200): JsonResponse
    {
        return (new ClientResource($client))->response()->setStatusCode($status);
    }

    public static function presentDeleted(): JsonResponse
    {
        return response()->json(['message' => 'Client deleted']);
    }
}

