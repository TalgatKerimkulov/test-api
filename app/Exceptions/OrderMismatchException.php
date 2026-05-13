<?php

declare(strict_types=1);

namespace App\Exceptions;

class OrderMismatchException extends DomainException
{
    public function __construct(
        public readonly int $orderId,
        public readonly int $orderItemId,
    ) {
        parent::__construct(sprintf(
            'order_item %d does not belong to order %d',
            $orderItemId, $orderId,
        ));
    }

    public function statusCode(): int
    {
        return 422;
    }

    public function toArray(): array
    {
        return [
            'error' => 'order_item_mismatch',
            'order_id' => $this->orderId,
            'order_item_id' => $this->orderItemId,
        ];
    }
}
