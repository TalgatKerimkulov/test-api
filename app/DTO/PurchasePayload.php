<?php

declare(strict_types=1);

namespace App\DTO;

use Carbon\CarbonImmutable;
use Illuminate\Http\Request;

final readonly class PurchasePayload
{
    /**
     * @param  PurchaseLine[]  $items
     */
    public function __construct(
        public int $providerId,
        public int $storageId,
        public CarbonImmutable $purchasedAt,
        public array $items,
    ) {}

    public static function fromRequest(Request $request): self
    {
        return new self(
            providerId: (int) $request->input('provider_id'),
            storageId: (int) $request->input('storage_id'),
            purchasedAt: CarbonImmutable::parse($request->input('purchased_at')),
            items: array_map(
                fn (array $i) => PurchaseLine::fromArray($i),
                $request->input('items', []),
            ),
        );
    }
}
