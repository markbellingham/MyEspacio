<?php

declare(strict_types=1);

namespace Tests\Photos\Domain\Entity;

use MyEspacio\Framework\DataSet;
use MyEspacio\Photos\Domain\Entity\Dimensions;
use PHPUnit\Framework\TestCase;

final class DimensionsTest extends TestCase
{
    public function testDimensions(): void
    {
        $dimensions = new Dimensions(456, 123);

        $this->assertSame(456, $dimensions->getWidth());
        $this->assertSame(123, $dimensions->getHeight());
        $this->assertEquals(
            [
                'width' => 456,
                'height' => 123
            ],
            $dimensions->jsonSerialize()
        );
    }

    public function testCreateFromDataset(): void
    {
        $data = new DataSet([
            'width' => 123,
            'height' => 456
        ]);
        $dimensions = Dimensions::createFromDataSet($data);
        $this->assertInstanceOf(Dimensions::class, $dimensions);
        $this->assertSame(123, $dimensions->getWidth());
        $this->assertSame(456, $dimensions->getHeight());
    }
}
