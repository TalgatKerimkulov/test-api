<?php

declare(strict_types=1);

namespace App\DTO;

use Carbon\CarbonImmutable;
use Illuminate\Http\Request;

final readonly class OrderPayload
{
    /**
     * @param  OrderLine[]  $products
     */
    public function __construct(
        public int $userId,
        public CarbonImmutable $orderedAt,
        public array $products,
    ) {}

    public static function fromRequest(Request $request): self
    {
        return new self(
            userId: (int) ($request->input('user_id') ?? $request->input('client_id')),
            orderedAt: $request->filled('ordered_at')
                ? CarbonImmutable::parse($request->input('ordered_at'))
                : CarbonImmutable::now(),
            products: array_map(
                fn (array $p) => OrderLine::fromArray($p),
                $request->input('products', []),
            ),
        );
    }
}
