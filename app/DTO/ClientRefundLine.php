<?php

declare(strict_types=1);

namespace App\DTO;

final readonly class ClientRefundLine
{
    public function __construct(
        public int $orderItemId,
        public int $qty,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            orderItemId: (int) $data['order_item_id'],
            qty: (int) $data['qty'],
        );
    }
}
