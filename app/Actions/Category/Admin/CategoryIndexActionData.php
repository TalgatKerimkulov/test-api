<?php

declare(strict_types=1);

namespace App\Actions\Category\Admin;

use Illuminate\Http\Request;

class CategoryIndexActionData
{
    public function __construct(
        public readonly bool $tree,
        public readonly int $page,
        public readonly int $limit,
    ) {
    }

    public static function fromRequest(Request $request): self
    {
        $limit = max(1, min(100, (int) $request->integer('limit', 20)));
        $page = max(1, (int) $request->integer('page', 1));

        return new self(
            tree: $request->boolean('tree', true),
            page: $page,
            limit: $limit,
        );
    }
}
