<?php

declare(strict_types=1);

namespace Tests\Php\Php\Common\Domain\Collection;

use MyEspacio\Common\Domain\Collection\CaptchaIconCollection;
use MyEspacio\Common\Domain\Entity\CaptchaIcon;
use MyEspacio\Framework\Exceptions\CollectionException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class CaptchaIconsCollectionTest extends TestCase
{
    /**
     * @param array<int, array<string, mixed>> $inputData
     * @param int $count
     * @param array<int, array<string, mixed>> $jsonSerialized
     */
    #[DataProvider('collectionDataProvider')]
    public function testCollection(
        array $inputData,
        int $count,
        array $jsonSerialized,
    ): void {
        $collection = new CaptchaIconCollection($inputData);

        foreach ($collection as $icon) {
            $this->assertInstanceOf(CaptchaIcon::class, $icon);
        }

        $this->assertCount($count, $collection);
        $this->assertEquals($jsonSerialized, $collection->jsonSerialize());
        $this->assertEquals($inputData, $collection->toArray());
    }

    /** @return array<string, array<string, mixed>> */
    public static function collectionDataProvider(): array
    {
        return [
            'two_elements' => [
                'inputData' => [
                    [
                        'icon_id' => '1',
                        'icon' => '<i class="bi bi-phone-vibrate"></i>',
                        'name' => 'Mobile'
                    ],
                    [
                        'icon_id' => '2',
                        'icon' => '<i class="bi bi-keyboard"></i>',
                        'name' => 'Keyboard'
                    ],
                ],
                'count' => 2,
                'jsonSerialized' => [
                    [
                        'name' => 'Mobile'
                    ],
                    [
                        'name' => 'Keyboard'
                    ],
                ],
            ],
            'one_element' => [
                'inputData' => [
                    [
                        'icon_id' => '2',
                        'icon' => '<i class="bi bi-keyboard"></i>',
                        'name' => 'Keyboard'
                    ],
                ],
                'count' => 1,
                'jsonSerialized' => [
                    [
                        'name' => 'Keyboard'
                    ],
                ],
            ],
            'zero_elements' => [
                'inputData' => [],
                'count' => 0,
                'jsonSerialized' => [],
            ],
        ];
    }

    public function testRequiredKeys(): void
    {
        $elements = [
            [
                'colour' => '1'
            ],
        ];

        $this->expectException(CollectionException::class);
        $this->expectExceptionMessage('Missing required keys: icon_id, icon, name');

        new CaptchaIconCollection($elements);
    }

    public function testException(): void
    {
        $elements = [
            'icon_id' => '1',
            'icon' => '<i class="bi bi-phone-vibrate"></i>',
            'name' => 'Mobile'
        ];

        $this->expectException(CollectionException::class);
        $this->expectExceptionMessage('The data passed is not an array.');

        // @phpstan-ignore argument.type
        new CaptchaIconCollection($elements);
    }
}
