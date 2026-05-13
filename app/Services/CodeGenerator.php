<?php

declare(strict_types=1);

namespace App\Services;

final class CodeGenerator
{
    public function generate(string $prefix): string
    {
        return sprintf('%s-%s-%04d',
            strtoupper($prefix),
            now()->format('YmdHis'),
            random_int(0, 9999),
        );
    }
}
