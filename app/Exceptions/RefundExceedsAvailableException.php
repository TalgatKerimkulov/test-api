<?php

declare(strict_types=1);

namespace App\Exceptions;

class RefundExceedsAvailableException extends DomainException
{
    public function __construct(
        public readonly int $batchItemId,
        public readonly int $requested,
        public readonly int $available,
    ) {
        parent::__construct(sprintf(
            'Purchase refund qty %d exceeds available %d on batch_item %d',
            $requested, $available, $batchItemId,
        ));
    }

    public function toArray(): array
    {
        return [
            'error' => 'refund_exceeds_available',
            'batch_item_id' => $this->batchItemId,
            'requested' => $this->requested,
            'available' => $this->available,
        ];
    }
}
