<?php

declare(strict_types=1);

namespace App\Actions\Provider\Common;

use App\Exceptions\RelationConflictException;
use App\Models\Provider;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

class ProviderAction
{
    public function index(Request $request): LengthAwarePaginator
    {
        return Provider::query()
            ->when($request->string('search')->toString(), fn ($q, $s) => $q->where('name', 'ilike', "%{$s}%"))
            ->orderBy('id')
            ->paginate((int) $request->integer('per_page', 15));
    }

    /** @param array<string,mixed> $data */
    public function store(array $data): Provider
    {
        return Provider::query()->create($data);
    }

    /** @param array<string,mixed> $data */
    public function update(Provider $provider, array $data): Provider
    {
        $provider->update($data);

        return $provider->fresh();
    }

    public function destroy(Provider $provider): void
    {
        if ($provider->batches()->exists() || $provider->categories()->exists()) {
            throw new RelationConflictException(
                'provider_has_relations',
                'Provider has related categories or batches and cannot be deleted.',
            );
        }
        $provider->delete();
    }
}

