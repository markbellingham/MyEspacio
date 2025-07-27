<?php

declare(strict_types=1);

namespace Tests\Common\Domain\Entity;

use MyEspacio\Common\Domain\Entity\Fave;
use MyEspacio\Framework\DataSet;
use MyEspacio\Framework\Exceptions\FaveException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

final class FaveTest extends TestCase
{
    #[DataProvider('modelDataProvider')]
    public function testModel(
        UuidInterface $userUuid,
        UuidInterface $itemUuid,
    ): void {
        $fave = new Fave($userUuid, $itemUuid);

        $this->assertSame($userUuid, $fave->getUserUuid());
        $this->assertSame($itemUuid, $fave->getItemUuid());
        $this->assertEquals([], $fave->jsonSerialize());
    }

    /** @return array<string, array<string, mixed>> */
    public static function modelDataProvider(): array
    {
        return [
            'test_1' => [
                'userUuid' => Uuid::fromString('c4f49300-a2af-40f5-b146-6c097a2d3f4d'),
                'itemUuid' => Uuid::fromString('7add56c3-ea9a-4c36-916e-a51a19c4bba1'),
            ],
            'test_2' => [
                'userUuid' => Uuid::fromString('51812b8b-a878-4e21-bc9a-e27350c43904'),
                'itemUuid' => Uuid::fromString('254b994d-fbb0-4f26-a99d-1da9f189df38'),
            ],
        ];
    }

    #[DataProvider('settersDataProvider')]
    public function testSetters(
        UuidInterface $userUuid,
        UuidInterface $itemUuid,
    ): void {
        $fave = new Fave(
            userUuid: Uuid::fromString('c4f49300-a2af-40f5-b146-6c097a2d3f4d'),
            itemUuid: Uuid::fromString('7add56c3-ea9a-4c36-916e-a51a19c4bba1')
        );

        $this->assertSame('c4f49300-a2af-40f5-b146-6c097a2d3f4d', $fave->getUserUuid()->toString());
        $this->assertSame('7add56c3-ea9a-4c36-916e-a51a19c4bba1', $fave->getItemUuid()->toString());

        $fave->setUserUuid($userUuid);
        $fave->setItemUuid($itemUuid);

        $this->assertSame($userUuid, $fave->getUserUuid());
        $this->assertSame($itemUuid, $fave->getItemUuid());
    }

    /** @return array<string, array<string, UuidInterface>> */
    public static function settersDataProvider(): array
    {
        return [
            'test_1' => [
                'userUuid' => Uuid::fromString('45849902-ebd5-4d2d-9e3d-fc3ad24d2c12'),
                'itemUuid' => Uuid::fromString('42e1354a-89d0-4785-bed7-948d81be9bec'),
            ],
            'test_2' => [
                'userUuid' => Uuid::fromString('51812b8b-a878-4e21-bc9a-e27350c43904'),
                'itemUuid' => Uuid::fromString('254b994d-fbb0-4f26-a99d-1da9f189df38'),
            ],
        ];
    }

    #[DataProvider('createFromDataSetDataProvider')]
    public function testCreateFromDataset(
        DataSet $dataSet,
        Fave $expectedFave,
    ): void {
        $fave = Fave::createFromDataSet($dataSet);
        $this->assertEquals($expectedFave, $fave);
    }

    /** @return array<string, array<string, mixed>> */
    public static function createFromDataSetDataProvider(): array
    {
        return [
            'test_1' => [
                'dataSet' => new DataSet([
                    'user_uuid' => '2cb35615-f812-45b9-b552-88a116979d11',
                    'item_uuid' => 'f133fede-65f5-4b68-aded-f8f0e9bfe3bb'
                ]),
                'expectedFave' => new Fave(
                    userUuid: Uuid::fromString('2cb35615-f812-45b9-b552-88a116979d11'),
                    itemUuid: Uuid::fromString('f133fede-65f5-4b68-aded-f8f0e9bfe3bb')
                ),
            ],
            'test_2' => [
                'dataSet' => new DataSet([
                    'user_uuid' => 'e2f0709f-b956-4595-98b7-1e90d2fd9a18',
                    'item_uuid' => 'aa91e0a8-31c7-4ad8-8fa7-1bec70a80d82'
                ]),
                'expectedFave' => new Fave(
                    userUuid: Uuid::fromString('e2f0709f-b956-4595-98b7-1e90d2fd9a18'),
                    itemUuid: Uuid::fromString('aa91e0a8-31c7-4ad8-8fa7-1bec70a80d82')
                ),
            ],
        ];
    }

    #[DataProvider('createFromDataSetExceptionDataProvider')]
    public function testCreateFromDataSetException(
        DataSet $dataSet
    ): void {
        $this->expectException(FaveException::class);
        $this->expectExceptionMessage('User uuid and item uuid must not be null.');

        Fave::createFromDataSet($dataSet);
    }

    /**
     * @return array<int, array<int, DataSet>>
     */
    public static function createFromDataSetExceptionDataProvider(): array
    {
        return [
            [
                new DataSet([
                    'user_uuid' => '2cb35615-f812-45b9-b552',
                    'item_uuid' => '3f9e14c1-6c5b-4d8c-a8d2-5c1dbbc43f67',
                ]),
            ],
            [
                new DataSet([
                    'user' => '3f9e14c1-6c5b-4d8c-a8d2-5c1dbbc43f67',
                    'item' => '3f9e14c1-6c5b-4d8c-a8d2',
                ]),
            ],
        ];
    }
}
