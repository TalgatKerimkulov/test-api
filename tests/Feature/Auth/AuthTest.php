<?php

declare(strict_types=1);

use App\Enums\Role;
use App\Models\User;

it('registers a new user and returns a token', function () {
    $response = $this->postJson('/api/v1/auth/register', [
        'name' => 'New User',
        'email' => 'new@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
    ]);

    $response->assertCreated();
    $response->assertJsonStructure(['user' => ['id', 'name', 'email', 'roles', 'permissions'], 'token']);
    $this->assertDatabaseHas('users', ['email' => 'new@example.com']);
});

it('rejects registration with invalid payload', function () {
    $response = $this->postJson('/api/v1/auth/register', [
        'name' => 'X',
        'email' => 'not-an-email',
        'password' => 'short',
    ]);

    $response->assertStatus(422);
});

it('logs in an existing user', function () {
    $user = User::factory()->create([
        'email' => 'login@example.com',
        'password' => 'password',
    ]);
    $user->syncRoles([\Spatie\Permission\Models\Role::findByName(Role::Manager->value, 'sanctum')]);

    $response = $this->postJson('/api/v1/auth/login', [
        'email' => 'login@example.com',
        'password' => 'password',
    ]);

    $response->assertOk();
    $response->assertJsonPath('user.email', 'login@example.com');
    $response->assertJsonStructure(['token']);
});

it('rejects login with bad credentials', function () {
    User::factory()->create(['email' => 'a@b.com', 'password' => 'password']);

    $response = $this->postJson('/api/v1/auth/login', [
        'email' => 'a@b.com', 'password' => 'wrong',
    ]);

    $response->assertStatus(422);
});

it('logs out the user and revokes the token', function () {
    $user = User::factory()->create();
    $user->syncRoles([\Spatie\Permission\Models\Role::findByName(Role::Manager->value, 'sanctum')]);
    $token = $user->createToken('api')->plainTextToken;

    $response = $this->withHeader('Authorization', 'Bearer '.$token)
        ->postJson('/api/v1/auth/logout');

    $response->assertOk();
    $response->assertJson(['message' => 'Successfully logged out']);
});

it('returns the current authenticated user from /me', function () {
    $user = User::factory()->create();
    $user->syncRoles([\Spatie\Permission\Models\Role::findByName(Role::Admin->value, 'sanctum')]);
    $this->actingAs($user, 'sanctum');

    $response = $this->getJson('/api/v1/auth/me');

    $response->assertOk();
    $response->assertJsonPath('data.id', $user->id);
    $response->assertJsonPath('data.roles.0', Role::Admin->value);
});

it('denies /me without authentication', function () {
    $response = $this->getJson('/api/v1/auth/me');
    $response->assertStatus(401);
});
