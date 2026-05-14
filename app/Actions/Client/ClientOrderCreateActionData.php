<?php

declare(strict_types=1);

namespace App\Actions\Client;

use App\DTO\OrderPayload;
use App\Enums\UserType;
use App\Exceptions\ApiException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class ClientOrderCreateActionData
{
    public function __construct(public readonly OrderPayload $payload)
    {
    }

    public static function fromRequest(Request $request): self
    {
        if ($request->has('client_id') && ! $request->has('user_id')) {
            $request->merge(['user_id' => $request->input('client_id')]);
        }

        $validator = Validator::make($request->all(), [
            'user_id' => [
                'required', 'integer',
                Rule::exists('users', 'id')
                    ->where(fn ($q) => $q->where('type', UserType::Client->value)
                        ->whereNull('deleted_at')),
            ],
            'ordered_at' => ['nullable', 'date'],
            'products' => ['required', 'array', 'min:1'],
            'products.*.id' => ['required', 'integer', 'exists:products,id', 'distinct'],
            'products.*.qty' => ['required', 'integer', 'min:1'],
        ]);

        if ($validator->fails()) {
            throw new ApiException($validator->errors()->first(), 422);
        }

        return new self(OrderPayload::fromRequest($request));
    }
}
