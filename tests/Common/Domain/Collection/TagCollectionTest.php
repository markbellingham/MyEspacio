<?php

declare(strict_types=1);

namespace Tests\Common\Domain\Collection;

use MyEspacio\Common\Domain\Collection\TagCollection;
use MyEspacio\Framework\Exceptions\CollectionException;
use PHPUnit\Framework\TestCase;

final class TagCollectionTest extends TestCase
{
    public function testTagCollection(): void
    {
        $data = [
            [
                'tag' => 'sunset',
                'id' => '1'
            ],
            [
                'tag' => 'flower',
                'id' => '2'
            ]
        ];

        $collection = new TagCollection($data);
        $this->assertInstanceOf(TagCollection::class, $collection);
        $this->assertCount(2, $collection);

        $tags = ['sunset', 'flower'];
        $ids = [1, 2];
        foreach ($collection as $tag) {
            $this->assertEquals(array_shift($tags), $tag->getTag());
            $this->assertSame(array_shift($ids), $tag->getId());
        }
    }

    public function testEmpty(): void
    {
        $collection = new TagCollection([]);
        $this->assertInstanceOf(TagCollection::class, $collection);
        $this->assertCount(0, $collection);
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

    public function testToArray(): void
    {
        $data = [
            [
                'tag' => 'sunset',
                'id' => '1'
            ],
            [
                'tag' => 'flower',
                'id' => '2'
            ]
        ];

        $collection = new TagCollection($data);
        $this->assertEquals($data, $collection->toArray());
    }

    public function testWrongDataType(): void
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
