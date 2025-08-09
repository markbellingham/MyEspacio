<?php

declare(strict_types=1);

namespace Tests\Photos\Domain\Entity;

use MyEspacio\Common\Domain\Entity\Tag;
use MyEspacio\Framework\DataSet;
use MyEspacio\Photos\Domain\Entity\PhotoTag;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

final class PhotoTagTest extends TestCase
{
    /** @param array<string, string> $jsonSerialized */
    #[DataProvider('modelDataProvider')]
    public function testModel(
        UuidInterface $photoUuid,
        string $tag,
        int $id,
        array $jsonSerialized,
    ): void {
        $photoTag = new PhotoTag($photoUuid, $tag, $id);

        $this->assertInstanceOf(Tag::class, $photoTag);

        $this->assertSame($photoUuid, $photoTag->getPhotoUuid());
        $this->assertSame($tag, $photoTag->getTag());
        $this->assertSame($id, $photoTag->getId());

        $this->assertEquals($jsonSerialized, $photoTag->jsonSerialize());
    }

    /** @return array<string, array<string, mixed>> */
    public static function modelDataProvider(): array
    {
        return [
            'test_1' => [
                'photoUuid' => Uuid::fromString('98951d80-139a-4745-adc8-2f15cb600fb1'),
                'tag' => 'bird',
                'id' => 45,
                'jsonSerialized' => [
                    'photoUuid' => '98951d80-139a-4745-adc8-2f15cb600fb1',
                    'tag' => 'bird',
                ],
            ],
            'test_2' => [
                'photoUuid' => Uuid::fromString('dfe8f20c-29cb-4023-b9a1-12802dbeaafc'),
                'tag' => 'dog',
                'id' => 37,
                'jsonSerialized' => [
                    'photoUuid' => 'dfe8f20c-29cb-4023-b9a1-12802dbeaafc',
                    'tag' => 'dog',
                ],
            ],
        ];
    }

    #[DataProvider('createFromDatasetDataProvider')]
    public function testCreateFromDataset(
        DataSet $dataset,
        PhotoTag $expectedModel,
    ): void {
        $photoTag = PhotoTag::createFromDataSet($dataset);
        $this->assertEquals($expectedModel, $photoTag);
    }

    /** @return array<string, array<string, mixed>> */
    public static function createFromDatasetDataProvider(): array
    {
        return [
            'test_1' => [
                'dataset' => new DataSet([
                    'photo_uuid' => '98951d80-139a-4745-adc8-2f15cb600fb1',
                    'tag' => 'bird',
                    'tag_id' => 45,
                ]),
                'expectedModel' => new PhotoTag(
                    photoUuid: Uuid::fromString('98951d80-139a-4745-adc8-2f15cb600fb1'),
                    tag: 'bird',
                    id: 45,
                ),
            ],
            'test_2' => [
                'dataset' => new DataSet([
                    'photo_uuid' => '622415f4-9551-4e0d-ada8-f8d1a387cc62',
                    'tag' => 'cat',
                    'tag_id' => 19,
                ]),
                'expectedModel' => new PhotoTag(
                    photoUuid: Uuid::fromString('622415f4-9551-4e0d-ada8-f8d1a387cc62'),
                    tag: 'cat',
                    id: 19,
                ),
            ]
        ];
    }
}
