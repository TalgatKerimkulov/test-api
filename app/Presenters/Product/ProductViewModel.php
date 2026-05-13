<?php

declare(strict_types=1);

namespace App\Presenters\Product;

use App\Presenters\ApiViewModel;
use Illuminate\Http\JsonResponse;

class ProductViewModel
{
    public static function present(mixed $result, int $status = 200): JsonResponse
    {
        return ApiViewModel::present($result, $status);
    }
}
