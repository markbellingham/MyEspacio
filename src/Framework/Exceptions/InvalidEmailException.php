<?php

declare(strict_types=1);

namespace MyEspacio\Framework\Exceptions;

use Exception;

final class InvalidEmailException extends Exception
{
    public static function invalidEmailAddress(): self
    {
        return new self('Invalid Email Address');
    }

    public static function invalidMessage(array $messageComponent): self
    {
        return new self('Invalid Message - ' . array_key_first($messageComponent) . ': ' . $messageComponent[array_key_first($messageComponent)]);
    }
}
