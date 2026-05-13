<?php

declare(strict_types=1);

namespace App\Actions\Category\Admin;

use Illuminate\Http\Request;

class CategoryItemListActionData
{
    public function __construct(public readonly ?string $search)
    {
    }

    public static function fromRequest(Request $request): self
    {
        $search = $request->filled('search') ? trim((string) $request->string('search')) : null;

        return new self($search);
    }
}
