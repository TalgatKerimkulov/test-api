<?php

declare(strict_types=1);

use App\Enums\Role;
use App\Models\User;

it('admin can create a user', function () {
    actingAsAdmin();

    $response = $this->postJson('/api/v1/users', [
        'name' => 'Manager User',
        'email' => 'manager@example.com',
        'password' => 'password',
        'role' => Role::Manager->value,
    ]);

    $response->assertCreated();
    $response->assertJsonPath('data.email', 'manager@example.com');
    $response->assertJsonPath('data.roles.0', Role::Manager->value);
});

it('admin can assign a role to a user', function () {
    actingAsAdmin();
    $user = User::factory()->create();

    $response = $this->postJson("/api/v1/users/{$user->id}/roles", [
        'role' => Role::Accountant->value,
    ]);

    $response->assertOk();
    $response->assertJsonPath('data.roles.0', Role::Accountant->value);
});

it('manager without users.create cannot create a user', function () {
    actingAsRole(Role::Manager->value);

    $response = $this->postJson('/api/v1/users', [
        'name' => 'X', 'email' => 'x@x.com', 'password' => 'password', 'role' => Role::Manager->value,
    ]);

    $response->assertStatus(403);
});

it('admin can list users', function () {
    actingAsAdmin();
    User::factory()->count(2)->create();

    $response = $this->getJson('/api/v1/users');
    $response->assertOk();
});
