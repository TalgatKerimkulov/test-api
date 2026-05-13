<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Enums\UserType;
use App\Enums\Role as RoleEnum;
use App\Http\Requests\Auth\ClientCompanyRequest;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Resources\UserResource;
use App\Models\Provider;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Spatie\Permission\Models\Role;

class AuthController
{
    public function register(RegisterRequest $request): JsonResponse
    {
        $user = User::create([
            'type' => UserType::Client->value,
            'name' => $request->string('name'),
            'email' => $request->string('email'),
            'password' => $request->string('password'),
        ]);
        $user->assignRole(Role::findByName(RoleEnum::Client->value, 'sanctum'));

        $token = $user->createToken('api')->plainTextToken;

        return response()->json([
            'user' => new UserResource($user),
            'token' => $token,
        ], 201);
    }

    public function login(LoginRequest $request): JsonResponse
    {
        return $this->performLogin($request, null);
    }

    public function adminLogin(LoginRequest $request): JsonResponse
    {
        return $this->performLogin($request, UserType::Admin);
    }

    public function clientLogin(LoginRequest $request): JsonResponse
    {
        return $this->performLogin($request, UserType::Client);
    }

    public function createClientCompany(ClientCompanyRequest $request): JsonResponse
    {
        $user = $request->user();
        if (! $user) {
            throw ValidationException::withMessages([
                'auth' => ['Unauthenticated.'],
            ]);
        }

        if ($user->type !== UserType::Client) {
            throw ValidationException::withMessages([
                'user' => ['Only clients can attach a company.'],
            ]);
        }

        $provider = DB::transaction(function () use ($request, $user): Provider {
            $provider = Provider::create($request->validated());
            $user->update(['provider_id' => $provider->id]);

            return $provider;
        });

        return response()->json([
            'provider' => $provider,
            'user' => new UserResource($user->fresh()),
        ], 201);
    }

    private function performLogin(LoginRequest $request, ?UserType $requiredType): JsonResponse
    {
        $user = User::where('email', $request->string('email'))->first();

        if (! $user || ! Hash::check((string) $request->string('password'), (string) $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        if ($requiredType !== null && $user->type !== $requiredType) {
            throw ValidationException::withMessages([
                'email' => ['This login endpoint does not allow the current user type.'],
            ]);
        }

        $token = $user->createToken('api')->plainTextToken;

        return response()->json([
            'user' => new UserResource($user),
            'token' => $token,
        ]);
    }

    public function logout(Request $request): JsonResponse
    {
        $user = $request->user();
        $token = $user?->currentAccessToken();
        if ($token && method_exists($token, 'delete')) {
            $token->delete();
        }

        return response()->json(['message' => 'Successfully logged out']);
    }

    public function me(Request $request): UserResource
    {
        return new UserResource($request->user());
    }
}
