<?php

declare(strict_types=1);

namespace App\Actions\Client;

use App\Models\User;
use Illuminate\Pagination\LengthAwarePaginator;

class ClientIndexAction
{
    public function handle(ClientIndexActionData $input): LengthAwarePaginator
    {
        return User::query()
            ->clients()
            ->when($input->search, fn ($q, $search) => $q->where(function ($q) use ($search): void {
                $q->where('name', 'ilike', "%{$search}%")
                    ->orWhere('email', 'ilike', "%{$search}%");
            }))
            ->orderBy('id')
            ->paginate($input->perPage);
    }
}

