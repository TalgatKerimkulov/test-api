<?php

declare(strict_types=1);

namespace App\Presenters\Provider;

use App\Presenters\ApiViewModel;
use Illuminate\Http\JsonResponse;

class ProviderViewModel
{
    public static function present(mixed $result, int $status = 200): JsonResponse
    {
        return ApiViewModel::present($result, $status);
    }
}
