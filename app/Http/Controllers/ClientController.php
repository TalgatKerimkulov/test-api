<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Enums\UserType;
use App\Exceptions\RelationConflictException;
use App\Http\Requests\Clients\StoreClientRequest;
use App\Http\Requests\Clients\UpdateClientRequest;
use App\Http\Resources\ClientResource;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

class ClientController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('permission:clients.view,sanctum', only: ['index', 'show']),
            new Middleware('permission:clients.create,sanctum', only: ['store']),
            new Middleware('permission:clients.update,sanctum', only: ['update']),
            new Middleware('permission:clients.delete,sanctum', only: ['destroy']),
        ];
    }

    public function index(Request $request): AnonymousResourceCollection
    {
        $clients = User::query()
            ->clients()
            ->when($request->string('search')->toString(), fn ($q, $s) => $q->where(function ($q) use ($s) {
                $q->where('name', 'ilike', "%$s%")->orWhere('email', 'ilike', "%$s%");
            }))
            ->orderBy('id')
            ->paginate((int) $request->integer('per_page', 15));

        return ClientResource::collection($clients);
    }

    public function store(StoreClientRequest $request): JsonResponse
    {
        $client = User::create([
            'type' => UserType::Client->value,
            'name' => $request->string('name'),
            'phone' => $request->input('phone'),
            'email' => $request->input('email'),
            'address' => $request->input('address'),
        ]);

        return (new ClientResource($client))->response()->setStatusCode(201);
    }

    public function show(User $client): ClientResource
    {
        abort_unless($client->isClient(), 404);

        return new ClientResource($client);
    }

    public function update(UpdateClientRequest $request, User $client): ClientResource
    {
        abort_unless($client->isClient(), 404);
        $client->update($request->validated());

        return new ClientResource($client->fresh());
    }

    public function destroy(User $client): JsonResponse
    {
        abort_unless($client->isClient(), 404);

        if ($client->orders()->exists()) {
            throw new RelationConflictException(
                'client_has_orders',
                'Client has orders and cannot be deleted.',
            );
        }

        $client->delete();

        return response()->json(['message' => 'Client deleted']);
    }
}
