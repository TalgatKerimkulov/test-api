<?php

declare(strict_types=1);

namespace App\Actions\Storage\Common;

use App\Exceptions\ApiException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class StorageStoreActionData
{
    /** @param array<string,mixed> $validated */
    public function __construct(public readonly array $validated) {}

    public static function fromRequest(Request $request): self
    {
        $validator = Validator::make($request->all(), [
            'name' => ['required', 'string', 'max:255'],
            'address' => ['nullable', 'string'],
        ]);
        if ($validator->fails()) {
            throw new ApiException($validator->errors()->first(), 422);
        }
        return new self($validator->validated());
    }
}

