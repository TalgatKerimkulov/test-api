<?php

declare(strict_types=1);

namespace App\Actions\Provider\Admin;

use Illuminate\Http\Request;

class ProviderIndexActionData
{
    public function __construct(
        public readonly ?string $search,
        public readonly int $page,
        public readonly int $limit,
    ) {
    }

    public static function fromRequest(Request $request): self
    {
        return new self(
            search: $request->filled('search') ? trim((string) $request->string('search')) : null,
            page: max(1, (int) $request->integer('page', 1)),
            limit: max(1, min(100, (int) $request->integer('limit', 20))),
        );
    }
}
