<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Enums\Role;
use App\Enums\UserType;
use App\Http\Requests\Users\StoreUserRequest;
use App\Http\Requests\Users\UpdateUserRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

class UserController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('permission:users.view,sanctum', only: ['index', 'show']),
            new Middleware('permission:users.create,sanctum', only: ['store']),
            new Middleware('permission:users.update,sanctum', only: ['update']),
            new Middleware('permission:users.delete,sanctum', only: ['destroy']),
        ];
    }

    public function index(Request $request): AnonymousResourceCollection
    {
        $users = User::query()
            ->staff()
            ->when($request->string('search')->toString(), fn ($q, $s) => $q->where(function ($q) use ($s) {
                $q->where('name', 'ilike', "%$s%")->orWhere('email', 'ilike', "%$s%");
            }))
            ->orderBy('id')
            ->paginate((int) $request->integer('per_page', 15));

        return UserResource::collection($users);
    }

    public function store(StoreUserRequest $request): JsonResponse
    {
        $user = User::create([
            'type' => $this->mapRoleToType($request->string('role')->toString()),
            'name' => $request->string('name'),
            'email' => $request->string('email'),
            'phone' => $request->input('phone'),
            'password' => $request->string('password'),
        ]);
        $user->syncRoles([$request->string('role')->toString()]);

        return (new UserResource($user->fresh()))->response()->setStatusCode(201);
    }

    public function show(User $user): UserResource
    {
        return new UserResource($user);
    }

    public function update(UpdateUserRequest $request, User $user): UserResource
    {
        $data = $request->only(['name', 'email', 'phone', 'password']);
        if ($request->filled('role')) {
            $data['type'] = $this->mapRoleToType($request->string('role')->toString());
        }
        $user->update($data);

        if ($request->filled('role')) {
            $user->syncRoles([$request->string('role')->toString()]);
        }

        return new UserResource($user->fresh());
    }

    public function destroy(User $user): JsonResponse
    {
        $user->tokens()->delete();
        $user->delete();

        return response()->json(['message' => 'User deactivated'], 200);
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
