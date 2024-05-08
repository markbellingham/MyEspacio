<?php

declare(strict_types=1);

namespace Tests\Framework\Exception;

use MyEspacio\Framework\Exceptions\InvalidEmailException;
use PHPUnit\Framework\TestCase;

class InvalidEmailExceptionTest extends TestCase
{
    public function testInvalidEmailAddressException(): void
    {
        $exception = InvalidEmailException::invalidEmailAddress();

        $this->assertInstanceOf(InvalidEmailException::class, $exception);
        $this->assertSame('Invalid Email Address', $exception->getMessage());
    }

    public function testInvalidMessageException(): void
    {
        $messageComponent = ['Key1' => 'Value1', 'Key2' => 'Value2'];
        $exception = InvalidEmailException::invalidMessage($messageComponent);

        $this->assertInstanceOf(InvalidEmailException::class, $exception);
        $this->assertSame('Invalid Message - Key1: Value1, Key2: Value2', $exception->getMessage());
    }
}
