<?php

declare(strict_types=1);

namespace Tests\Framework\Exception;

use MyEspacio\Framework\Exceptions\CollectionException;
use PHPUnit\Framework\TestCase;

final class CollectionExceptionTest extends TestCase
{
    public function testWrongDataType()
    {
        $exception = CollectionException::wrongDataType();
        $this->assertInstanceOf(CollectionException::class, $exception);
        $this->assertSame('The data passed is not an array.', $exception->getMessage());
    }

    public function testMissingRequiredValues()
    {
        $exception = CollectionException::missingRequiredValues([]);
        $this->assertInstanceOf(CollectionException::class, $exception);
        $this->assertSame('Missing required values: ', $exception->getMessage());
    }
}
