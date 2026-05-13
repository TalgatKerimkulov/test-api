<?php

declare(strict_types=1);

namespace App\Actions\Product\Admin;

use Illuminate\Http\Request;

class ProductIndexActionData
{
    public function __construct(
        public readonly ?int $categoryId,
        public readonly ?int $providerId,
        public readonly ?string $name,
        public readonly int $page,
        public readonly int $limit,
    ) {
    }

    public static function fromRequest(Request $request): self
    {
        $filter = (array) $request->input('filter', []);

        return new self(
            categoryId: isset($filter['category_id']) ? (int) $filter['category_id'] : null,
            providerId: isset($filter['provider_id']) ? (int) $filter['provider_id'] : null,
            name: isset($filter['name']) ? trim((string) $filter['name']) : null,
            page: max(1, (int) $request->integer('page', 1)),
            limit: max(1, min(100, (int) $request->integer('limit', 20))),
        );
    }
}
