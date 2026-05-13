<?php

declare(strict_types=1);

namespace App\Actions\Provider\Admin;

use Illuminate\Http\Request;

class ProviderItemListActionData
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
