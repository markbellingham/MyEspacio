<?php

declare(strict_types=1);

namespace Tests\Framework\Exception;

use MyEspacio\Framework\Exceptions\DirectoryException;
use PHPUnit\Framework\TestCase;

final class DirectoryExceptionTest extends TestCase
{
    public function testDirectoryDoesNotExist(): void
    {
        $noneExistantDirectory = 'nonexistent/directory';
        $exception = DirectoryException::directoryDoesNotExist($noneExistantDirectory);
        $this->assertInstanceOf(DirectoryException::class, $exception);
        $this->assertEquals('Directory does not exist: ' . $noneExistantDirectory, $exception->getMessage());
    }
}
