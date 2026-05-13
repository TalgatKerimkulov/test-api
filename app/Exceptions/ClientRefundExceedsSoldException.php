<?php

declare(strict_types=1);

namespace App\Exceptions;

class ClientRefundExceedsSoldException extends DomainException
{
    public function __construct(
        public readonly int $orderItemId,
        public readonly int $requested,
        public readonly int $refundable,
    ) {
        parent::__construct(sprintf(
            'Client refund qty %d exceeds refundable %d on order_item %d',
            $requested, $refundable, $orderItemId,
        ));
    }

    public function toArray(): array
    {
        return [
            'error' => 'refund_exceeds_sold',
            'order_item_id' => $this->orderItemId,
            'requested' => $this->requested,
            'refundable' => $this->refundable,
        ];
    }
}
