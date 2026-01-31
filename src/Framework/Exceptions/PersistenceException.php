<?php

declare(strict_types=1);

namespace MyEspacio\Framework\Exceptions;

use RuntimeException;
use Throwable;

final class PersistenceException extends RuntimeException
{
    public static function persistenceFailed(Throwable $e): self
    {
        return new self('Failed to persist data.', $e->getCode(), $e);
    }
}
