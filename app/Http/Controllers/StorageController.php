<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Exceptions\RelationConflictException;
use App\Http\Requests\Storages\StoreStorageRequest;
use App\Http\Requests\Storages\UpdateStorageRequest;
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

    public function index(Request $request): AnonymousResourceCollection
    {
        $storages = Storage::query()->orderBy('id')->paginate((int) $request->integer('per_page', 15));

        return StorageResource::collection($storages);
    }

    public function store(StoreStorageRequest $request): JsonResponse
    {
        $storage = Storage::create($request->validated());

        return (new StorageResource($storage))->response()->setStatusCode(201);
    }

    public function show(Storage $storage): StorageResource
    {
        return new StorageResource($storage);
    }

    public function update(UpdateStorageRequest $request, Storage $storage): StorageResource
    {
        $storage->update($request->validated());

        return new StorageResource($storage->fresh());
    }

    public function destroy(Storage $storage): JsonResponse
    {
        if ($storage->stocks()->where('qty', '>', 0)->exists() || $storage->stockMovements()->exists()) {
            throw new RelationConflictException(
                'storage_in_use',
                'Storage has stock or stock movements and cannot be deleted.',
            );
        }
        $storage->delete();

        return response()->json(['message' => 'Storage deleted']);
    }
}
