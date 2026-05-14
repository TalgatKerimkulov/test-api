<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Actions\Auth\Common\AuthAction;
use App\Actions\Auth\Common\ClientCompanyActionData;
use App\Actions\Auth\Common\LoginActionData;
use App\Actions\Auth\Common\RegisterActionData;
use App\Enums\UserType;
use App\Http\Resources\UserResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class AuthController
{
    public function register(Request $request, AuthAction $service): JsonResponse
    {
        [$user, $token] = $service->register(RegisterActionData::fromRequest($request)->validated);

        return response()->json([
            'user' => new UserResource($user),
            'token' => $token,
        ], 201);
    }

    public function login(Request $request, AuthAction $service): JsonResponse
    {
        return $this->performLogin($request, $service, null);
    }

    public function adminLogin(Request $request, AuthAction $service): JsonResponse
    {
        return $this->performLogin($request, $service, UserType::Admin);
    }

    public function clientLogin(Request $request, AuthAction $service): JsonResponse
    {
        return $this->performLogin($request, $service, UserType::Client);
    }

    public function createClientCompany(Request $request, AuthAction $service): JsonResponse
    {
        $user = $request->user();
        if (! $user) {
            throw ValidationException::withMessages([
                'auth' => ['Unauthenticated.'],
            ]);
        }

        $input = ClientCompanyActionData::fromRequest($request);
        [$provider, $freshUser] = $service->createClientCompany($user, $input->validated);

        return response()->json([
            'provider' => $provider,
            'user' => new UserResource($freshUser),
        ], 201);
    }

    private function performLogin(Request $request, AuthAction $service, ?UserType $requiredType): JsonResponse
    {
        [$user, $token] = $service->login(LoginActionData::fromRequest($request)->validated, $requiredType);

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
