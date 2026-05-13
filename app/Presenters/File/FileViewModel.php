<?php

declare(strict_types=1);

namespace App\Presenters\File;

use App\Presenters\ApiViewModel;
use Illuminate\Http\JsonResponse;

class FileViewModel
{
    public static function present(mixed $result, int $status = 200): JsonResponse
    {
        return ApiViewModel::present($result, $status);
    }
}
