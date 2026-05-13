<?php

declare(strict_types=1);

namespace App\DTO;

final readonly class PurchaseRefundLine
{
    public function __construct(
        public int $batchItemId,
        public int $qty,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            batchItemId: (int) $data['batch_item_id'],
            qty: (int) $data['qty'],
        );
    }
}
