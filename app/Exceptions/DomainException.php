<?php

declare(strict_types=1);

namespace App\Exceptions;

use RuntimeException;

abstract class DomainException extends RuntimeException
{
    /**
     * @return array<string, mixed>
     */
    abstract public function toArray(): array;

    public function statusCode(): int
    {
        return 409;
    }
}
