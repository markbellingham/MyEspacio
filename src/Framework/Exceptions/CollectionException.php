<?php

declare(strict_types=1);

namespace MyEspacio\Framework\Exceptions;

use Exception;

final class CollectionException extends Exception
{
    public static function wrongDataType(): CollectionException
    {
        return new self('The data passed is not an array.');
    }

    /**
     * @param array<int, string> $missingKeys
     * @return CollectionException
     */
    public static function missingRequiredValues(array $missingKeys): CollectionException
    {
        return new self('Missing required keys: ' . implode(', ', $missingKeys));
    }
}
