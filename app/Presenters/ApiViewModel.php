<?php

declare(strict_types=1);

namespace App\Presenters;

use Illuminate\Http\JsonResponse;

class ApiViewModel
{
    public static function present(mixed $result, int $status = 200): JsonResponse
    {
        return response()->json([
            'success' => true,
            'result' => $result,
        ], $status);
    }
}
