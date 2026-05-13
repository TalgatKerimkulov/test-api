<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\Users\AssignRoleRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

class UserRoleController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [new Middleware('permission:roles.assign,sanctum')];
    }

    public function store(AssignRoleRequest $request, User $user): UserResource
    {
        $user->syncRoles([$request->string('role')->toString()]);

        return new UserResource($user->fresh());
    }
}
