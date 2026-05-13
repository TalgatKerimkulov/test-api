<?php

declare(strict_types=1);

namespace App\DTO;

final readonly class PurchaseLine
{
    public function __construct(
        public int $productId,
        public int $qty,
        public string $purchasePrice,
        public string $salePrice,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            productId: (int) $data['product_id'],
            qty: (int) $data['qty'],
            purchasePrice: (string) $data['purchase_price'],
            salePrice: (string) $data['sale_price'],
        );
    }
}
