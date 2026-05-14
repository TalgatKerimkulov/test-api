<?php

declare(strict_types=1);

namespace App\Actions\Client;

use App\DTO\ClientRefundPayload;
use App\Exceptions\ApiException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ClientRefundCreateActionData
{
    public function __construct(public readonly ClientRefundPayload $payload)
    {
    }

    public static function fromRequest(Request $request): self
    {
        $validator = Validator::make($request->all(), [
            'order_id' => ['required', 'integer', 'exists:orders,id'],
            'refunded_at' => ['required', 'date'],
            'reason' => ['nullable', 'string', 'max:1000'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.order_item_id' => ['required', 'integer', 'exists:order_items,id'],
            'items.*.qty' => ['required', 'integer', 'min:1'],
        ]);

        if ($validator->fails()) {
            throw new ApiException($validator->errors()->first(), 422);
        }

        return new self(ClientRefundPayload::fromRequest($request));
    }
}
