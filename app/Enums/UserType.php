<?php

declare(strict_types=1);

namespace App\Enums;

enum UserType: string
{
    case Client = 'client';
    case Admin = 'admin';
    case Manager = 'manager';
    case Employee = 'employee';

    public static function values(): array
    {
        return array_map(fn (self $c) => $c->value, self::cases());
    }
}
