<?php

declare(strict_types=1);

namespace MyEspacio\Framework\Files;

use FilesystemIterator;
use MyEspacio\Framework\Exceptions\DirectoryException;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

final class DirectoryReader
{
    /**
     * @param string $directory
     * @return RecursiveIteratorIterator<RecursiveDirectoryIterator>
     * @throws DirectoryException
     */
    public static function getFiles(string $directory): RecursiveIteratorIterator
    {
        if (!is_dir($directory)) {
            throw DirectoryException::directoryDoesNotExist($directory);
        }

        return new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($directory, FilesystemIterator::SKIP_DOTS),
            RecursiveIteratorIterator::SELF_FIRST
        );
    }
}
