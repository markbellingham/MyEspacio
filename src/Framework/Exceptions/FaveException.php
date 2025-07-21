<?php

declare(strict_types=1);

namespace MyEspacio\Framework\Exceptions;

final class FaveException extends \Exception
{
    public static function noNullValues(): FaveException
    {
        return new self('User uuid and item uuid must not be null.');
    }
}
