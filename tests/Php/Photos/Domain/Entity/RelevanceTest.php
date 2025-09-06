<?php

declare(strict_types=1);

namespace Tests\Php\Php\Photos\Domain\Entity;

use MyEspacio\Framework\DataSet;
use MyEspacio\Photos\Domain\Entity\Relevance;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class RelevanceTest extends TestCase
{
    /** @param array<string, int> $jsonSerialized */
    #[DataProvider('modelDataProvider')]
    public function testModel(
        int $cScore,
        int $pScore,
        array $jsonSerialized,
    ): void {
        $relevance = new Relevance($cScore, $pScore);

        $this->assertSame($cScore, $relevance->getCScore());
        $this->assertSame($pScore, $relevance->getPScore());
        $this->assertEquals($jsonSerialized, $relevance->jsonSerialize());
    }

    /** @return array<string, array<string, mixed>> */
    public static function modelDataProvider(): array
    {
        return [
            'test_1' => [
                'cScore' => 4,
                'pScore' => 5,
                'jsonSerialized' => [
                    'cScore' => 4,
                    'pScore' => 5,
                ],
            ],
            'test_2' => [
                'cScore' => 7,
                'pScore' => 9,
                'jsonSerialized' => [
                    'cScore' => 7,
                    'pScore' => 9,
                ],
            ]
        ];
    }

    #[DataProvider('createFromDatasetDataProvider')]
    public function testCreateFromDataset(
        DataSet $dataset,
        Relevance $expectedModel,
    ): void {
        $relevance = Relevance::createFromDataSet($dataset);
        $this->assertEquals($expectedModel, $relevance);
    }

    /** @return array<string, array<string, mixed>> */
    public static function createFromDatasetDataProvider(): array
    {
        return [
            'test_1' => [
                'dataset' => new DataSet([
                    'cscore' => '4',
                    'pscore' => '5'
                ]),
                'expectedModel' => new Relevance(
                    cScore: 4,
                    pScore: 5,
                )
            ],
            'test_2' => [
                'dataset' => new DataSet([
                    'cscore' => '7',
                    'pscore' => '9'
                ]),
                'expectedModel' => new Relevance(
                    cScore: 7,
                    pScore: 9,
                ),
            ],
        ];
    }
}
