<?php

declare(strict_types=1);

namespace App\Actions\Provider\Admin;

use App\Exceptions\ApiException;
use App\Models\Provider;

class ProviderShowAction
{
    public function handle(ProviderShowActionData $input): array
    {
        $provider = Provider::query()->find($input->id);
        if (! $provider) {
            throw new ApiException('Provider not found', 404);
        }

        return $provider->toArray();
    }
}
