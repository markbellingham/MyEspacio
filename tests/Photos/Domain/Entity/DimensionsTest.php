<?php

declare(strict_types=1);

namespace Tests\Photos\Domain\Entity;

use MyEspacio\Framework\DataSet;
use MyEspacio\Photos\Domain\Entity\Dimensions;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class DimensionsTest extends TestCase
{
    /** @param array<string, int> $jsonSerialized */
    #[DataProvider('dimensionsDataProvider')]
    public function testDimensions(
        int $width,
        int $height,
        array $jsonSerialized,
    ): void {
        $dimensions = new Dimensions($width, $height);

        $this->assertSame($width, $dimensions->getWidth());
        $this->assertSame($height, $dimensions->getHeight());
        $this->assertEquals($jsonSerialized, $dimensions->jsonSerialize());
    }

    /** @return array<string, array<string, mixed>> */
    public static function dimensionsDataProvider(): array
    {
        return [
            'test_1' => [
                'width' => 123,
                'height' => 456,
                'jsonSerialized' => [
                    'width' => 123,
                    'height' => 456,
                ],
            ],
            'test_2' => [
                'width' => 321,
                'height' => 654,
                'jsonSerialized' => [
                    'width' => 321,
                    'height' => 654,
                ],
            ]
        ];
    }

    #[DataProvider('createFromDatasetDataProvider')]
    public function testCreateFromDataset(
        DataSet $dataset,
        Dimensions $expectedModel,
    ): void {
        $dimensions = Dimensions::createFromDataSet($dataset);
        $this->assertEquals($expectedModel, $dimensions);
    }

    /** @return array<string, array<string, mixed>> */
    public static function createFromDatasetDataProvider(): array
    {
        return [
            'test_1' => [
                'dataset' => new DataSet([
                    'width' => 123,
                    'height' => 456,
                ]),
                'expectedModel' => new Dimensions(
                    width: 123,
                    height: 456,
                ),
            ],
            'test_2' => [
                'dataset' => new DataSet([
                    'width' => 321,
                    'height' => 654,
                ]),
                'expectedModel' => new Dimensions(
                    width: 321,
                    height: 654,
                ),
            ],
        ];
    }
}
