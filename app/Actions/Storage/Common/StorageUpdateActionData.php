<?php

declare(strict_types=1);

namespace App\Actions\Storage\Common;

use App\Exceptions\ApiException;
use App\Models\Storage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class StorageUpdateActionData
{
    /** @param array<string,mixed> $validated */
    public function __construct(public readonly array $validated, public readonly Storage $storage) {}

    public static function fromRequest(Request $request, Storage $storage): self
    {
        $validator = Validator::make($request->all(), [
            'name' => ['sometimes', 'string', 'max:255'],
            'address' => ['sometimes', 'nullable', 'string'],
        ]);
        if ($validator->fails()) {
            throw new ApiException($validator->errors()->first(), 422);
        }
        return new self($validator->validated(), $storage);
    }
}

