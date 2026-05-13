<?php

declare(strict_types=1);

namespace App\Enums;

enum BatchStatus: string
{
    case Draft = 'draft';
    case Completed = 'completed';
    case PartiallyRefunded = 'partially_refunded';
    case Refunded = 'refunded';
    case Cancelled = 'cancelled';

    public static function values(): array
    {
        return array_map(fn (self $c) => $c->value, self::cases());
    }
}
