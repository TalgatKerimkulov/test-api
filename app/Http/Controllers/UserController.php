<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\User\Common\UserAction;
use App\Actions\User\Common\UserStoreActionData;
use App\Actions\User\Common\UserUpdateActionData;
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

    public function index(Request $request, UserAction $service): AnonymousResourceCollection
    {
        $users = $service->index($request);

        return UserResource::collection($users);
    }

    public function store(Request $request, UserAction $service): JsonResponse
    {
        $user = $service->store(UserStoreActionData::fromRequest($request)->validated);

        return (new UserResource($user->fresh()))->response()->setStatusCode(201);
    }

    public function show(User $user): UserResource
    {
        return new UserResource($user);
    }

    public function update(Request $request, User $user, UserAction $service): UserResource
    {
        $input = UserUpdateActionData::fromRequest($request, $user);
        return new UserResource($service->update($input->user, $input->validated));
    }

    public function destroy(User $user, UserAction $service): JsonResponse
    {
        $service->destroy($user);

        return response()->json(['message' => 'User deactivated'], 200);
    }
}
