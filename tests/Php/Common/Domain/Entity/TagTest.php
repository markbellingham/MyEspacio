<?php

declare(strict_types=1);

namespace Tests\Php\Common\Domain\Entity;

use MyEspacio\Common\Domain\Entity\Tag;
use MyEspacio\Framework\DataSet;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class TagTest extends TestCase
{
    /** @param array<string, mixed> $jsonSerialized */
    #[DataProvider('modelDataProvider')]
    public function testModel(
        string $tag,
        int $id,
        array $jsonSerialized,
    ): void {
        $model = new Tag($tag, $id);

        $this->assertSame($tag, $model->getTag());
        $this->assertSame($id, $model->getId());
        $this->assertEquals($jsonSerialized, $model->jsonSerialize());
    }

    /** @return array<string, array<string, mixed>> */
    public static function modelDataProvider(): array
    {
        return [
            'test_1' => [
                'tag' => 'sunset',
                'id' => 1,
                'jsonSerialized' => [
                    'tag' => 'sunset',
                ],
            ],
            'test_2' => [
                'tag' => 'mexico',
                'id' => 2,
                'jsonSerialized' => [
                    'tag' => 'mexico',
                ],
            ],
        ];
    }

    public function testTagNoId(): void
    {
        $tag = new Tag(
            tag: 'sunset'
        );

        $this->assertEquals('sunset', $tag->getTag());
        $this->assertNull($tag->getId());
    }

    #[DataProvider('settersDataProvider')]
    public function testSetters(
        string $tag,
        int $id,
    ): void {
        $model = new Tag(
            tag: 'sunset'
        );

        $this->assertEquals('sunset', $model->getTag());
        $this->assertNull($model->getId());

        $model->setTag($tag);
        $model->setId($id);

        $this->assertEquals($tag, $model->getTag());
        $this->assertSame($id, $model->getId());
    }

    /** @return array<string, array<string, mixed>> */
    public static function settersDataProvider(): array
    {
        return [
            'test_1' => [
                'tag' => 'river',
                'id' => 5,
            ],
            'test_2' => [
                'tag' => 'sunset',
                'id' => 1,
            ],
        ];
    }

    #[DataProvider('createFromDataSetDataProvider')]
    public function testCreateFromDataset(
        DataSet $dataSet,
        Tag $expectedTag,
    ): void {
        $tag = Tag::createFromDataSet($dataSet);
        $this->assertEquals($expectedTag, $tag);
    }

    /** @return array<string, array<string, mixed>> */
    public static function createFromDataSetDataProvider(): array
    {
        return [
            'test_1' => [
                'dataSet' => new DataSet([
                    'tag' => 'sunset',
                    'id' => '1'
                ]),
                'expectedTag' => new Tag(
                    tag: 'sunset',
                    id: 1
                ),
            ],
        ];
    }
}
