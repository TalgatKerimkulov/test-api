<?php

declare(strict_types=1);

namespace App\Actions\Provider\Admin;

use App\Models\Provider;

class ProviderItemListAction
{
    /**
     * @return array<int, array{id:int,name:string}>
     */
    public function handle(ProviderItemListActionData $input): array
    {
        return Provider::query()
            ->when($input->search, fn ($q, $s) => $q->where('name', 'ilike', "%{$s}%"))
            ->orderBy('name')
            ->limit(100)
            ->get(['id', 'name'])
            ->map(fn (Provider $provider): array => [
                'id' => $provider->id,
                'name' => $provider->name,
            ])
            ->values()
            ->all();
    }
}
