<?php

declare(strict_types=1);

namespace Tests\Php\Framework\Files;

use MyEspacio\Framework\Files\DirectoryReader;
use MyEspacio\Framework\Exceptions\DirectoryException;
use PHPUnit\Framework\TestCase;
use RecursiveIteratorIterator;

final class DirectoryReaderTest extends TestCase
{
    public function testGetFilesReturnsIteratorForValidDirectory(): void
    {
        $directory = __DIR__ . '/temp';
        $expected = 2;

        $iterator = DirectoryReader::getFiles($directory);

        $this->assertInstanceOf(RecursiveIteratorIterator::class, $iterator);
        $this->assertCount($expected, $iterator);
    }

    public function testGetFilesThrowsExceptionForInvalidDirectory(): void
    {
        $directory = 'invalid';

        $this->expectException(DirectoryException::class);
        $this->expectExceptionMessage('Directory does not exist: ' . $directory);

        DirectoryReader::getFiles($directory);
    }
}
