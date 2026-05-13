<?php

declare(strict_types=1);

namespace App\Actions\Provider\Admin;

use App\Exceptions\ApiException;
use App\Models\Provider;
use Illuminate\Support\Facades\DB;

class ProviderDeleteAction
{
    public function handle(ProviderDeleteActionData $input): bool
    {
        $provider = Provider::query()->find($input->id);
        if (! $provider) {
            throw new ApiException('Provider not found', 404);
        }

        if ($provider->batches()->exists() || $provider->categories()->exists()) {
            throw new ApiException('Provider has related categories or batches and cannot be deleted.', 409);
        }

        DB::transaction(function () use ($provider): void {
            $provider->delete();
        });

        return true;
    }
}
