<?php

declare(strict_types=1);

use App\Enums\Permission;
use App\Enums\Role;
use App\Models\User;

function makeUserWithRole(string $role): User
{
    $roleModel = \Spatie\Permission\Models\Role::findByName($role, 'sanctum');
    $user = User::factory()->create();
    $user->syncRoles([$roleModel]);

    return $user;
}

it('admin has all permissions', function () {
    $user = makeUserWithRole(Role::Admin->value);

    foreach (Permission::values() as $p) {
        expect($user->hasPermissionTo($p, 'sanctum'))->toBeTrue("Admin missing $p");
    }
});

it('warehouse_manager can create purchase, manager cannot', function () {
    $warehouse = makeUserWithRole(Role::WarehouseManager->value);
    $manager = makeUserWithRole(Role::Manager->value);

    expect($warehouse->hasPermissionTo(Permission::PurchasesCreate->value, 'sanctum'))->toBeTrue();
    expect($manager->hasPermissionTo(Permission::PurchasesCreate->value, 'sanctum'))->toBeFalse();
});

it('accountant can view batch profit report', function () {
    $user = makeUserWithRole(Role::Accountant->value);

    expect($user->hasPermissionTo(Permission::ReportsBatchProfit->value, 'sanctum'))->toBeTrue();
});

it('returns 403 when user lacks the required permission', function () {
    actingAsRole(Role::Manager->value);

    $response = $this->postJson('/api/v1/purchases', []);
    $response->assertStatus(403);
});

it('returns 401 when the request is unauthenticated', function () {
    $response = $this->postJson('/api/v1/purchases', []);
    $response->assertStatus(401);
});
