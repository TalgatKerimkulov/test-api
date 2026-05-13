<?php

declare(strict_types=1);

namespace App\Actions\Category\Admin;

use App\Exceptions\ApiException;
use Illuminate\Http\Request;

class CategoryShowActionData
{
    public function __construct(public readonly int $id)
    {
    }

    public static function fromRequest(Request $request): self
    {
        $id = (int) $request->integer('id');
        if ($id <= 0) {
            throw new ApiException('Category id is required', 422);
        }

        return new self($id);
    }
}
