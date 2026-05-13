<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class BatchProfitRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'from' => ['nullable', 'date'],
            'to' => ['nullable', 'date', 'after_or_equal:from'],
            'batch_id' => ['nullable', 'integer', 'exists:batches,id'],
            'provider_id' => ['nullable', 'integer', 'exists:providers,id'],
        ];
    }
}
