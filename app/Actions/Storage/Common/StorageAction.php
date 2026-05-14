<?php

declare(strict_types=1);

namespace App\Actions\Storage\Common;

use App\Exceptions\RelationConflictException;
use App\Models\Storage;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

class StorageAction
{
    public function index(Request $request): LengthAwarePaginator
    {
        return Storage::query()->orderBy('id')->paginate((int) $request->integer('per_page', 15));
    }

    /** @param array<string,mixed> $data */
    public function store(array $data): Storage
    {
        return Storage::query()->create($data);
    }

    /** @param array<string,mixed> $data */
    public function update(Storage $storage, array $data): Storage
    {
        $storage->update($data);

        return $storage->fresh();
    }

    public function destroy(Storage $storage): void
    {
        if ($storage->stocks()->where('qty', '>', 0)->exists() || $storage->stockMovements()->exists()) {
            throw new RelationConflictException(
                'storage_in_use',
                'Storage has stock or stock movements and cannot be deleted.',
            );
        }
        $storage->delete();
    }
}

