<?php

declare(strict_types=1);

namespace Tests\Php\Common\Domain\Collection;

use MyEspacio\Common\Domain\Collection\TagCollection;
use MyEspacio\Common\Domain\Entity\Tag;
use MyEspacio\Framework\Exceptions\CollectionException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class TagCollectionTest extends TestCase
{
    /**
     * @param array<int, array<string, mixed>> $inputData
     * @param int $count
     * @param array<int, array<string, mixed>> $jsonSerialized
     * @return void
     */
    #[DataProvider('collectionDataProvider')]
    public function testTagCollection(
        array $inputData,
        int $count,
        array $jsonSerialized,
    ): void {
        $collection = new TagCollection($inputData);

        foreach ($collection as $tag) {
            $this->assertInstanceOf(Tag::class, $tag);
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
                        'tag' => 'sunset',
                        'id' => '1'
                    ],
                    [
                        'tag' => 'flower',
                        'id' => '2'
                    ],
                ],
                'count' => 2,
                'jsonSerialized' => [
                    [
                        'tag' => 'sunset',
                    ],
                    [
                        'tag' => 'flower',
                    ],
                ],
            ],
            'one_element' => [
                'inputData' => [
                    [
                        'tag' => 'sunset',
                        'id' => '1'
                    ],
                ],
                'count' => 1,
                'jsonSerialized' => [
                    [
                        'tag' => 'sunset',
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
        $data = [
            [
                'id' => '1'
            ]
        ];

        $this->expectException(CollectionException::class);
        $this->expectExceptionMessage('Missing required keys: tag');
        new TagCollection($data);
    }

    public function testException(): void
    {
        $data = [
            'tag' => 'sunset',
            'id' => '1'
        ];

        $this->expectException(CollectionException::class);
        $this->expectExceptionMessage('The data passed is not an array.');

        // @phpstan-ignore argument.type
        new TagCollection($data);
    }
}
