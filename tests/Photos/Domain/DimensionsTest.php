<?php

declare(strict_types=1);

namespace Tests\Photos\Domain;

use MyEspacio\Photos\Domain\Dimensions;
use PHPUnit\Framework\TestCase;

final class DimensionsTest extends TestCase
{
    public function testDimensions(): void
    {
        $dimensions = new Dimensions(456, 123);

        $this->assertSame(456, $dimensions->getWidth());
        $this->assertSame(123, $dimensions->getHeight());
    }
}
