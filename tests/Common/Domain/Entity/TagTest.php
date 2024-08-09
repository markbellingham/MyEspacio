<?php

declare(strict_types=1);

namespace Tests\Common\Domain\Entity;

use MyEspacio\Common\Domain\Entity\Tag;
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
}
