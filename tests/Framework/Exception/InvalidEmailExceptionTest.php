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
        $messageComponent = ['Value1', 'Value2'];
        $exception = InvalidEmailException::invalidMessage($messageComponent);

        $this->assertInstanceOf(InvalidEmailException::class, $exception);

        if (!empty($messageComponent)) {
            $expectedMessage = 'Invalid Message - ' . array_key_first($messageComponent) . ': ' . $messageComponent[0];
        } else {
            $expectedMessage = 'Invalid Message';
        }

        $this->assertSame($expectedMessage, $exception->getMessage());
    }
}
