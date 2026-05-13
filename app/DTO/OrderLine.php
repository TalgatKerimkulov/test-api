<?php

declare(strict_types=1);

namespace App\DTO;

final readonly class OrderLine
{
    public function __construct(
        public int $productId,
        public int $qty,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            productId: (int) $data['id'],
            qty: (int) $data['qty'],
        );
    }
}
