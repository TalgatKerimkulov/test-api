<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\Storage\Common\StorageAction;
use App\Actions\Storage\Common\StorageStoreActionData;
use App\Actions\Storage\Common\StorageUpdateActionData;
use App\Http\Resources\StorageResource;
use App\Models\Storage;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

class StorageController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('permission:storages.view,sanctum', only: ['index', 'show']),
            new Middleware('permission:storages.create,sanctum', only: ['store']),
            new Middleware('permission:storages.update,sanctum', only: ['update']),
            new Middleware('permission:storages.delete,sanctum', only: ['destroy']),
        ];
    }

    public function index(Request $request, StorageAction $service): AnonymousResourceCollection
    {
        $storages = $service->index($request);

        return StorageResource::collection($storages);
    }

    public function store(Request $request, StorageAction $service): JsonResponse
    {
        $storage = $service->store(StorageStoreActionData::fromRequest($request)->validated);

        return (new StorageResource($storage))->response()->setStatusCode(201);
    }

    public function show(Storage $storage): StorageResource
    {
        return new StorageResource($storage);
    }

    public function update(Request $request, Storage $storage, StorageAction $service): StorageResource
    {
        $input = StorageUpdateActionData::fromRequest($request, $storage);
        return new StorageResource($service->update($input->storage, $input->validated));
    }

    public function destroy(Storage $storage, StorageAction $service): JsonResponse
    {
        $service->destroy($storage);

        return response()->json(['message' => 'Storage deleted']);
    }
}
