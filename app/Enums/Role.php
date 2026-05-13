<?php

declare(strict_types=1);

namespace App\Enums;

enum Role: string
{
    case Admin = 'admin';
    case Manager = 'manager';
    case Accountant = 'accountant';
    case WarehouseManager = 'warehouse_manager';

    public static function values(): array
    {
        return array_map(fn (self $r) => $r->value, self::cases());
    }
}
