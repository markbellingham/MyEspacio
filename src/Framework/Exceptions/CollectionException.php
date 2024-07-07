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

    public static function missingRequiredKeys(): CollectionException
    {
        return new self('The REQUIRED_KEYS constant has not been defined.');
    }

    public static function missingRequiredValues(array $missingKeys): CollectionException
    {
        return new self('Missing required values: ' . implode(', ', $missingKeys));
    }
}
