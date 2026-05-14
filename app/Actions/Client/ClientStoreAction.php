<?php

declare(strict_types=1);

namespace App\Actions\Client;

use App\Enums\UserType;
use App\Models\User;

class ClientStoreAction
{
    public function handle(ClientStoreActionData $input): User
    {
        return User::query()->create([
            'type' => UserType::Client->value,
            'name' => $input->name,
            'phone' => $input->phone,
            'email' => $input->email,
            'address' => $input->address,
        ]);
    }
}

