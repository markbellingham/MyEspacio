<?php

declare(strict_types=1);

namespace Tests\Common\Domain\Entity;

use MyEspacio\Common\Domain\Entity\Fave;
use MyEspacio\Framework\DataSet;
use PHPUnit\Framework\TestCase;

final class FaveTest extends TestCase
{
    public function testFave(): void
    {
        $fave = new Fave(2, 1);

        $this->assertSame(2, $fave->getUserId());
        $this->assertSame(1, $fave->getItemId());
    }

    public function testFaveSetters(): void
    {
        $fave = new Fave(2, 1);
        $this->assertSame(2, $fave->getUserId());
        $this->assertSame(1, $fave->getItemId());

        $fave->setUserId(3);
        $fave->setItemId(5);

        $this->assertSame(3, $fave->getUserId());
        $this->assertSame(5, $fave->getItemId());
    }

    public function testCreateFromDataset(): void
    {
        $data = new DataSet([
            'user_id' => 2,
            'item_id' => 1
        ]);

        $fave = Fave::createFromDataSet($data);
        $this->assertInstanceOf(Fave::class, $fave);
        $this->assertSame(2, $fave->getUserId());
        $this->assertSame(1, $fave->getItemId());
    }
}
