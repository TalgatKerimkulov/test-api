<?php

use App\Enums\Role as RoleEnum;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

pest()->extend(TestCase::class)
    ->use(RefreshDatabase::class)
    ->beforeEach(function (): void {
        app(PermissionRegistrar::class)->forgetCachedPermissions();
        $this->seed(RolesAndPermissionsSeeder::class);
    })
    ->in('Feature');

expect()->extend('toBeOne', function () {
    return $this->toBe(1);
});

function actingAsRole(string $role): User
{
    $roleModel = \Spatie\Permission\Models\Role::findByName($role, 'sanctum');
    $user = User::factory()->create();
    $user->syncRoles([$roleModel]);
    test()->actingAs($user, 'sanctum');

    return $user;
}

function actingAsAdmin(): User
{
    return actingAsRole(RoleEnum::Admin->value);
}
