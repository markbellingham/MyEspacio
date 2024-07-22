<?php

declare(strict_types=1);

namespace Tests\Photos\Domain\Entity;

use PHPUnit\Framework\TestCase;

final class DimensionsTest extends TestCase
{
    public function testDimensions(): void
    {
        $dimensions = new \MyEspacio\Photos\Domain\Entity\Dimensions(456, 123);

        $this->assertSame(456, $dimensions->getWidth());
        $this->assertSame(123, $dimensions->getHeight());
    }
}
