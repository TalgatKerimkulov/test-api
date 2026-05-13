<?php

declare(strict_types=1);

namespace App\Exceptions;

class BatchRefundMismatchException extends DomainException
{
    public function __construct(
        public readonly int $batchId,
        public readonly int $batchItemId,
    ) {
        parent::__construct(sprintf(
            'batch_item %d does not belong to batch %d',
            $batchItemId, $batchId,
        ));
    }

    public function statusCode(): int
    {
        return 422;
    }

    public function toArray(): array
    {
        return [
            'error' => 'batch_item_mismatch',
            'batch_id' => $this->batchId,
            'batch_item_id' => $this->batchItemId,
        ];
    }
}
