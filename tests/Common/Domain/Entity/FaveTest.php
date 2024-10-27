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
        $fave = new Fave(
            userUuid: 'c4f49300-a2af-40f5-b146-6c097a2d3f4d',
            itemUuid: '7add56c3-ea9a-4c36-916e-a51a19c4bba1'
        );

        $this->assertSame('c4f49300-a2af-40f5-b146-6c097a2d3f4d', $fave->getUserUuid());
        $this->assertSame('7add56c3-ea9a-4c36-916e-a51a19c4bba1', $fave->getItemUuid());
    }

    public function testFaveSetters(): void
    {
        $fave = new Fave(
            userUuid: 'c4f49300-a2af-40f5-b146-6c097a2d3f4d',
            itemUuid: '7add56c3-ea9a-4c36-916e-a51a19c4bba1'
        );
        $this->assertSame('c4f49300-a2af-40f5-b146-6c097a2d3f4d', $fave->getUserUuid());
        $this->assertSame('7add56c3-ea9a-4c36-916e-a51a19c4bba1', $fave->getItemUuid());

        $fave->setUserUuid('51812b8b-a878-4e21-bc9a-e27350c43904');
        $fave->setItemUuid('254b994d-fbb0-4f26-a99d-1da9f189df38');

        $this->assertSame('51812b8b-a878-4e21-bc9a-e27350c43904', $fave->getUserUuid());
        $this->assertSame('254b994d-fbb0-4f26-a99d-1da9f189df38', $fave->getItemUuid());
    }

    public function testCreateFromDataset(): void
    {
        $data = new DataSet([
            'user_uuid' => '2cb35615-f812-45b9-b552-88a116979d11',
            'item_uuid' => 'f133fede-65f5-4b68-aded-f8f0e9bfe3bb'
        ]);

        $fave = Fave::createFromDataSet($data);
        $this->assertInstanceOf(Fave::class, $fave);
        $this->assertSame('2cb35615-f812-45b9-b552-88a116979d11', $fave->getUserUuid());
        $this->assertSame('f133fede-65f5-4b68-aded-f8f0e9bfe3bb', $fave->getItemUuid());
    }
}
