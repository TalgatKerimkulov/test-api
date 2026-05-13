<?php

declare(strict_types=1);

namespace App\Actions\Provider\Admin;

use App\Exceptions\ApiException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class ProviderUpdateActionData
{
    /**
     * @param array<string, mixed> $payload
     */
    public function __construct(
        public readonly int $id,
        public readonly array $payload,
    ) {
    }

    public static function fromRequest(Request $request): self
    {
        $id = (int) ($request->input('id') ?? 0);
        if ($id <= 0) {
            throw new ApiException('Provider id is required', 422);
        }

        $validator = Validator::make($request->all(), [
            'id' => ['required', 'integer', 'exists:providers,id'],
            'name' => ['sometimes', 'string', 'max:255'],
            'inn' => ['sometimes', 'nullable', 'string', 'max:32', Rule::unique('providers', 'inn')->ignore($id)->whereNull('deleted_at')],
            'email' => ['sometimes', 'nullable', 'email', 'max:255'],
            'phone' => ['sometimes', 'nullable', 'string', 'max:32'],
        ]);

        if ($validator->fails()) {
            throw new ApiException($validator->errors()->first(), 422);
        }

        $data = $validator->validated();
        unset($data['id']);

        return new self($id, $data);
    }
}
