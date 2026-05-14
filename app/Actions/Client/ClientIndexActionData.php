<?php

declare(strict_types=1);

namespace App\Actions\Client;

use App\Exceptions\ApiException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ClientIndexActionData
{
    public function __construct(
        public readonly ?string $search,
        public readonly int $perPage,
    ) {
    }

    public static function fromRequest(Request $request): self
    {
        $validator = Validator::make($request->all(), [
            'search' => ['nullable', 'string'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
        ]);

        if ($validator->fails()) {
            throw new ApiException($validator->errors()->first(), 422);
        }

        $search = $request->string('search')->toString();

        return new self(
            search: $search !== '' ? $search : null,
            perPage: (int) $request->integer('per_page', 15),
        );
    }
}
