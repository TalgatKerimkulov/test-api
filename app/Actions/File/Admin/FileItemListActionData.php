<?php

declare(strict_types=1);

namespace App\Actions\File\Admin;

use Illuminate\Http\Request;

class FileItemListActionData
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
