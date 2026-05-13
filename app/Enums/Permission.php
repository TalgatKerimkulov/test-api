<?php

declare(strict_types=1);

namespace App\Enums;

enum Permission: string
{
    case UsersView = 'users.view';
    case UsersCreate = 'users.create';
    case UsersUpdate = 'users.update';
    case UsersDelete = 'users.delete';

    case RolesView = 'roles.view';
    case RolesCreate = 'roles.create';
    case RolesUpdate = 'roles.update';
    case RolesDelete = 'roles.delete';
    case RolesAssign = 'roles.assign';

    case ProvidersView = 'providers.view';
    case ProvidersCreate = 'providers.create';
    case ProvidersUpdate = 'providers.update';
    case ProvidersDelete = 'providers.delete';

    case ClientsView = 'clients.view';
    case ClientsCreate = 'clients.create';
    case ClientsUpdate = 'clients.update';
    case ClientsDelete = 'clients.delete';

    case CategoriesView = 'categories.view';
    case CategoriesCreate = 'categories.create';
    case CategoriesUpdate = 'categories.update';
    case CategoriesDelete = 'categories.delete';

    case ProductsView = 'products.view';
    case ProductsCreate = 'products.create';
    case ProductsUpdate = 'products.update';
    case ProductsDelete = 'products.delete';

    case StoragesView = 'storages.view';
    case StoragesCreate = 'storages.create';
    case StoragesUpdate = 'storages.update';
    case StoragesDelete = 'storages.delete';

    case PurchasesView = 'purchases.view';
    case PurchasesCreate = 'purchases.create';
    case PurchasesRefund = 'purchases.refund';
    case BatchesView = 'batches.view';
    case BatchesProfit = 'batches.profit';

    case ClientOrdersView = 'client_orders.view';
    case ClientOrdersCreate = 'client_orders.create';
    case ClientOrdersRefund = 'client_orders.refund';

    case ReportsStockRemaining = 'reports.stock_remaining';
    case ReportsBatchProfit = 'reports.batch_profit';

    public static function values(): array
    {
        return array_map(fn (self $p) => $p->value, self::cases());
    }
}
