<?php

declare(strict_types=1);

namespace App\Actions\Product\Admin;

use Illuminate\Http\Request;

class ProductItemListActionData
{
    public function __construct(public readonly ?string $search)
    {
    }

    public static function fromRequest(Request $request): self
    {
        return new self(
            search: $request->filled('search') ? trim((string) $request->string('search')) : null,
        );
    }
}
