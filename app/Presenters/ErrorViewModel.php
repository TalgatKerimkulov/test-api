<?php

declare(strict_types=1);

namespace App\Presenters;

use Illuminate\Http\JsonResponse;

class ErrorViewModel
{
    public static function present(string $message, int $status = 200, ?string $code = null): JsonResponse
    {
        return response()->json([
            'success' => false,
            'error' => [
                'message' => $message,
                'code' => $code ?? $status,
            ],
            'result' => null,
        ], $status);
    }
}
