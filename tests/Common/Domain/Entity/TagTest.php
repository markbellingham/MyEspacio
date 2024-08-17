<?php

declare(strict_types=1);

namespace Tests\Common\Domain\Entity;

use MyEspacio\Common\Domain\Entity\Tag;
use MyEspacio\Framework\DataSet;
use PHPUnit\Framework\TestCase;

final class TagTest extends TestCase
{
    public function testTag(): void
    {
        $tag = new Tag(
            tag: 'sunset',
            id: 1
        );

        $this->assertEquals('sunset', $tag->getTag());
        $this->assertSame(1, $tag->getId());
    }

    public function testTagNoId(): void
    {
        $tag = new Tag(
            tag: 'sunset'
        );

        $this->assertEquals('sunset', $tag->getTag());
        $this->assertNull($tag->getId());
    }

    public function testSetters(): void
    {
        $tag = new Tag(
            tag: 'sunset'
        );

        $this->assertEquals('sunset', $tag->getTag());
        $this->assertNull($tag->getId());

        $tag->setTag('river');
        $tag->setId(1);

        $this->assertEquals('river', $tag->getTag());
        $this->assertSame(1, $tag->getId());
    }

    public function testJsonSerialize(): void
    {
        $tag = new Tag(
            tag: 'sunset',
            id: 1
        );
        $this->assertEquals(
            [
                'tag' => 'sunset'
            ],
            $tag->jsonSerialize()
        );
    }

    public function testCreateFromDataset(): void
    {
        $data = new DataSet([
            'tag' => 'sunset',
            'id' => 1
        ]);

        $tag = Tag::createFromDataSet($data);
        $this->assertInstanceOf(Tag::class, $tag);
        $this->assertEquals('sunset', $tag->getTag());
        $this->assertSame(1, $tag->getId());
    }
}
