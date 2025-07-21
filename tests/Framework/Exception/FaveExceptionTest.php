<?php

declare(strict_types=1);

namespace Framework\Exception;

use MyEspacio\Framework\Exceptions\FaveException;
use PHPUnit\Framework\TestCase;

final class FaveExceptionTest extends TestCase
{
    public function testNoNullValues(): void
    {
        $this->expectException(FaveException::class);
        $this->expectExceptionMessage('User uuid and item uuid must not be null.');

        throw FaveException::noNullValues();
    }
}
