<?php

declare(strict_types=1);

namespace App\Actions\Provider\Admin;

use App\Exceptions\ApiException;
use App\Models\Provider;
use Illuminate\Support\Facades\DB;

class ProviderUpdateAction
{
    public function handle(ProviderUpdateActionData $input): array
    {
        $provider = Provider::query()->find($input->id);
        if (! $provider) {
            throw new ApiException('Provider not found', 404);
        }

        DB::transaction(function () use ($provider, $input): void {
            $provider->update($input->payload);
        });

        return $provider->fresh()->toArray();
    }
}
