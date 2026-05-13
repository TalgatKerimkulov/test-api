<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\Permission as PermissionEnum;
use App\Enums\Role as RoleEnum;
use App\Enums\UserType;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        foreach (PermissionEnum::values() as $permission) {
            Permission::findOrCreate($permission, 'sanctum');
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $adminRole = Role::findOrCreate(RoleEnum::Admin->value, 'sanctum');
        $clientRole = Role::findOrCreate(RoleEnum::Client->value, 'sanctum');
        $managerRole = Role::findOrCreate(RoleEnum::Manager->value, 'sanctum');
        $accountantRole = Role::findOrCreate(RoleEnum::Accountant->value, 'sanctum');
        $warehouseRole = Role::findOrCreate(RoleEnum::WarehouseManager->value, 'sanctum');

        $adminRole->syncPermissions(PermissionEnum::values());

        $managerRole->syncPermissions([
            PermissionEnum::ClientsView->value,
            PermissionEnum::ClientsCreate->value,
            PermissionEnum::ClientsUpdate->value,
            PermissionEnum::ProductsView->value,
            PermissionEnum::CategoriesView->value,
            PermissionEnum::StoragesView->value,
            PermissionEnum::ClientOrdersView->value,
            PermissionEnum::ClientOrdersCreate->value,
            PermissionEnum::ClientOrdersRefund->value,
            PermissionEnum::ReportsStockRemaining->value,
        ]);

        $accountantRole->syncPermissions([
            PermissionEnum::ProvidersView->value,
            PermissionEnum::ClientsView->value,
            PermissionEnum::CategoriesView->value,
            PermissionEnum::ProductsView->value,
            PermissionEnum::StoragesView->value,
            PermissionEnum::PurchasesView->value,
            PermissionEnum::BatchesView->value,
            PermissionEnum::BatchesProfit->value,
            PermissionEnum::ClientOrdersView->value,
            PermissionEnum::ReportsStockRemaining->value,
            PermissionEnum::ReportsBatchProfit->value,
        ]);

        $warehouseRole->syncPermissions([
            PermissionEnum::ProvidersView->value,
            PermissionEnum::ProvidersCreate->value,
            PermissionEnum::ProvidersUpdate->value,
            PermissionEnum::CategoriesView->value,
            PermissionEnum::ProductsView->value,
            PermissionEnum::ProductsCreate->value,
            PermissionEnum::ProductsUpdate->value,
            PermissionEnum::StoragesView->value,
            PermissionEnum::StoragesCreate->value,
            PermissionEnum::StoragesUpdate->value,
            PermissionEnum::PurchasesView->value,
            PermissionEnum::PurchasesCreate->value,
            PermissionEnum::PurchasesRefund->value,
            PermissionEnum::BatchesView->value,
            PermissionEnum::ReportsStockRemaining->value,
        ]);

        $clientRole->syncPermissions([
            PermissionEnum::ClientsView->value,
            PermissionEnum::ClientsCreate->value,
            PermissionEnum::ClientsUpdate->value,
            PermissionEnum::ProductsView->value,
            PermissionEnum::ProductsCreate->value,
            PermissionEnum::ProductsUpdate->value,
            PermissionEnum::CategoriesView->value,
            PermissionEnum::StoragesView->value,
            PermissionEnum::PurchasesView->value,
            PermissionEnum::PurchasesCreate->value,
            PermissionEnum::PurchasesRefund->value,
            PermissionEnum::ClientOrdersView->value,
            PermissionEnum::ClientOrdersCreate->value,
            PermissionEnum::ClientOrdersRefund->value,
            PermissionEnum::ReportsStockRemaining->value,
        ]);

        $admin = User::withTrashed()->firstOrNew(['email' => 'admin@example.com']);
        $admin->fill([
            'type' => UserType::Admin->value,
            'name' => 'Admin',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
        ]);
        if ($admin->trashed()) {
            $admin->restore();
        }
        $admin->save();
        $admin->syncRoles([$adminRole]);

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
