<?php

declare(strict_types=1);

namespace App\Enums;

enum StockMovementType: string
{
    case Purchase = 'purchase';
    case Sale = 'sale';
    case PurchaseRefund = 'purchase_refund';
    case ClientRefund = 'client_refund';
    case Adjustment = 'adjustment';

    public static function values(): array
    {
        return array_map(fn (self $c) => $c->value, self::cases());
    }
}
