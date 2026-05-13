<?php

declare(strict_types=1);

namespace App\Actions\Provider\Admin;

use App\Models\Provider;
use Illuminate\Support\Facades\DB;

class ProviderCreateAction
{
    public function handle(ProviderCreateActionData $input): array
    {
        $provider = DB::transaction(function () use ($input): Provider {
            return Provider::query()->create([
                'name' => $input->name,
                'inn' => $input->inn,
                'email' => $input->email,
                'phone' => $input->phone,
            ]);
        });

        return $provider->toArray();
    }
}
