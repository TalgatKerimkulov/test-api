<?php

declare(strict_types=1);

namespace App\DTO;

use Carbon\CarbonImmutable;
use Illuminate\Http\Request;

final readonly class PurchaseRefundPayload
{
    /**
     * @param  PurchaseRefundLine[]  $items
     */
    public function __construct(
        public int $batchId,
        public CarbonImmutable $refundedAt,
        public ?string $reason,
        public array $items,
    ) {}

    public static function fromRequest(Request $request): self
    {
        return new self(
            batchId: (int) $request->input('batch_id'),
            refundedAt: CarbonImmutable::parse($request->input('refunded_at')),
            reason: $request->input('reason'),
            items: array_map(
                fn (array $i) => PurchaseRefundLine::fromArray($i),
                $request->input('items', []),
            ),
        );
    }
}
