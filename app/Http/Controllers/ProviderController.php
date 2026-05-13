<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Exceptions\RelationConflictException;
use App\Http\Requests\Providers\StoreProviderRequest;
use App\Http\Requests\Providers\UpdateProviderRequest;
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

    public function index(Request $request): AnonymousResourceCollection
    {
        $providers = Provider::query()
            ->when($request->string('search')->toString(), fn ($q, $s) => $q->where('name', 'ilike', "%$s%"))
            ->orderBy('id')
            ->paginate((int) $request->integer('per_page', 15));

        return ProviderResource::collection($providers);
    }

    public function store(StoreProviderRequest $request): JsonResponse
    {
        $provider = Provider::create($request->validated());

        return (new ProviderResource($provider))->response()->setStatusCode(201);
    }

    public function show(Provider $provider): ProviderResource
    {
        return new ProviderResource($provider);
    }

    public function update(UpdateProviderRequest $request, Provider $provider): ProviderResource
    {
        $provider->update($request->validated());

        return new ProviderResource($provider->fresh());
    }

    public function destroy(Provider $provider): JsonResponse
    {
        if ($provider->batches()->exists() || $provider->categories()->exists()) {
            throw new RelationConflictException(
                'provider_has_relations',
                'Provider has related categories or batches and cannot be deleted.',
            );
        }
        $provider->delete();

        return response()->json(['message' => 'Provider deleted']);
    }
}
