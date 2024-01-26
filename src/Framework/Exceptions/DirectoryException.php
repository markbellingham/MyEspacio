<?php

declare(strict_types=1);

namespace MyEspacio\Framework\Exceptions;

use Exception;

final class DirectoryException extends Exception
{
    public static function directoryDoesNotExist(string $directory): self
    {
        return new self('Directory does not exist: ' . $directory);
    }
}
