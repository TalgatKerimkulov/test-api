<?php

declare(strict_types=1);

namespace App\Exceptions;

class RelationConflictException extends DomainException
{
    public function __construct(private readonly string $error, string $message)
    {
        parent::__construct($message);
    }

    public function toArray(): array
    {
        return [
            'error' => $this->error,
            'message' => $this->getMessage(),
        ];
    }

    public function statusCode(): int
    {
        return 409;
    }
}
