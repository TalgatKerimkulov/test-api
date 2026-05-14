<?php

declare(strict_types=1);

namespace App\Actions\User\Common;

use App\Enums\Role;
use App\Enums\UserType;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

class UserAction
{
    public function index(Request $request): LengthAwarePaginator
    {
        return User::query()
            ->staff()
            ->when($request->string('search')->toString(), fn ($q, $s) => $q->where(function ($q) use ($s): void {
                $q->where('name', 'ilike', "%{$s}%")->orWhere('email', 'ilike', "%{$s}%");
            }))
            ->orderBy('id')
            ->paginate((int) $request->integer('per_page', 15));
    }

    /** @param array<string,mixed> $data */
    public function store(array $data): User
    {
        $role = (string) $data['role'];
        $user = User::query()->create([
            'type' => $this->mapRoleToType($role),
            'name' => $data['name'],
            'email' => $data['email'],
            'phone' => $data['phone'] ?? null,
            'password' => $data['password'],
        ]);
        $user->syncRoles([$role]);

        return $user->fresh();
    }

    /** @param array<string,mixed> $data */
    public function update(User $user, array $data): User
    {
        $update = array_intersect_key($data, array_flip(['name', 'email', 'phone', 'password']));
        if (isset($data['role'])) {
            $update['type'] = $this->mapRoleToType((string) $data['role']);
        }
        $user->update($update);
        if (isset($data['role'])) {
            $user->syncRoles([(string) $data['role']]);
        }

        return $user->fresh();
    }

    public function destroy(User $user): void
    {
        $user->tokens()->delete();
        $user->delete();
    }

    private function mapRoleToType(string $role): string
    {
        return match ($role) {
            Role::Admin->value => UserType::Admin->value,
            Role::Manager->value => UserType::Manager->value,
            Role::Accountant->value, Role::WarehouseManager->value => UserType::Employee->value,
            default => UserType::Employee->value,
        };
    }
}

