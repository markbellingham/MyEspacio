<?php

declare(strict_types=1);

namespace Tests\Common\Domain\Entity;

use MyEspacio\Common\Domain\Entity\Fave;
use PHPUnit\Framework\TestCase;

final class FaveTest extends TestCase
{
    public function testFave(): void
    {
        $fave = new Fave(2, 1);

        $this->assertEquals(2, $fave->getUserId());
        $this->assertEquals(1, $fave->getItemId());
        $this->assertEquals(
            [
                'user_id' => 2,
                'item_id' => 1
            ],
            $fave->jsonSerialize()
        );
    }

    public function testFaveSetters(): void
    {
        $fave = new Fave(2, 1);
        $this->assertEquals(2, $fave->getUserId());
        $this->assertEquals(1, $fave->getItemId());

        $fave->setUserId(3);
        $fave->setItemId(5);

        $this->assertEquals(3, $fave->getUserId());
        $this->assertEquals(5, $fave->getItemId());
    }
}
