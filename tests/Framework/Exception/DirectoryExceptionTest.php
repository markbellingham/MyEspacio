<?php

declare(strict_types=1);

namespace Tests\Framework\Exception;

use MyEspacio\Framework\Exceptions\DirectoryException;
use PHPUnit\Framework\TestCase;

class DirectoryExceptionTest extends TestCase
{
    public function testDirectoryDoesNotExist()
    {
        $exception = DirectoryException::directoryDoesNotExist();
        $this->assertInstanceOf(DirectoryException::class, $exception);
        $this->assertEquals('Directory does not exist', $exception->getMessage());
    }
}
