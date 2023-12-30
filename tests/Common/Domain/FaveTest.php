<?php

declare(strict_types=1);

namespace Tests\Common\Domain;

use MyEspacio\Common\Domain\Fave;
use PHPUnit\Framework\TestCase;

final class FaveTest extends TestCase
{
    public function testFave()
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
}
