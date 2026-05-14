<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\Provider\Common\ProviderAction;
use App\Actions\Provider\Common\ProviderStoreActionData;
use App\Actions\Provider\Common\ProviderUpdateActionData;
use App\Http\Resources\ProviderResource;
use App\Models\Provider;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

class ProviderController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('permission:providers.view,sanctum', only: ['index', 'show']),
            new Middleware('permission:providers.create,sanctum', only: ['store']),
            new Middleware('permission:providers.update,sanctum', only: ['update']),
            new Middleware('permission:providers.delete,sanctum', only: ['destroy']),
        ];
    }

    public function index(Request $request, ProviderAction $service): AnonymousResourceCollection
    {
        $providers = $service->index($request);

        return ProviderResource::collection($providers);
    }

    public function store(Request $request, ProviderAction $service): JsonResponse
    {
        $provider = $service->store(ProviderStoreActionData::fromRequest($request)->validated);

        return (new ProviderResource($provider))->response()->setStatusCode(201);
    }

    public function show(Provider $provider): ProviderResource
    {
        return new ProviderResource($provider);
    }

    public function update(Request $request, Provider $provider, ProviderAction $service): ProviderResource
    {
        $input = ProviderUpdateActionData::fromRequest($request, $provider);
        return new ProviderResource($service->update($input->provider, $input->validated));
    }

    public function destroy(Provider $provider, ProviderAction $service): JsonResponse
    {
        $service->destroy($provider);

        return response()->json(['message' => 'Provider deleted']);
    }
}
