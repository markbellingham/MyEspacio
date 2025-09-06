<?php

declare(strict_types=1);

namespace Tests\Php\Framework\Exception;

use MyEspacio\Framework\Exceptions\CollectionException;
use PHPUnit\Framework\TestCase;

final class CollectionExceptionTest extends TestCase
{
    public function testWrongDataType(): void
    {
        $exception = CollectionException::wrongDataType();
        $this->assertInstanceOf(CollectionException::class, $exception);
        $this->assertSame('The data passed is not an array.', $exception->getMessage());
    }

    public function testMissingRequiredValues(): void
    {
        $exception = CollectionException::missingRequiredValues([]);
        $this->assertInstanceOf(CollectionException::class, $exception);
        $this->assertSame('Missing required keys: ', $exception->getMessage());
    }
}
