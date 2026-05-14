<?php

declare(strict_types=1);

namespace App\Actions\Auth\Common;

use App\Enums\Role as RoleEnum;
use App\Enums\UserType;
use App\Models\Provider;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Spatie\Permission\Models\Role;

class AuthAction
{
    /** @param array<string,mixed> $data */
    public function register(array $data): array
    {
        $user = User::query()->create([
            'type' => UserType::Client->value,
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => $data['password'],
        ]);
        $user->assignRole(Role::findByName(RoleEnum::Client->value, 'sanctum'));
        $token = $user->createToken('api')->plainTextToken;

        return [$user, $token];
    }

    /** @param array<string,mixed> $credentials */
    public function login(array $credentials, ?UserType $requiredType): array
    {
        $user = User::query()->where('email', $credentials['email'])->first();
        if (! $user || ! Hash::check((string) $credentials['password'], (string) $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }
        if ($requiredType !== null && $user->type !== $requiredType) {
            throw ValidationException::withMessages([
                'email' => ['This login endpoint does not allow the current user type.'],
            ]);
        }

        return [$user, $user->createToken('api')->plainTextToken];
    }

    /** @param array<string,mixed> $data */
    public function createClientCompany(User $user, array $data): array
    {
        if ($user->type !== UserType::Client) {
            throw ValidationException::withMessages([
                'user' => ['Only clients can attach a company.'],
            ]);
        }

        $provider = DB::transaction(function () use ($user, $data): Provider {
            $provider = Provider::query()->create($data);
            $user->update(['provider_id' => $provider->id]);

            return $provider;
        });

        return [$provider, $user->fresh()];
    }
}

