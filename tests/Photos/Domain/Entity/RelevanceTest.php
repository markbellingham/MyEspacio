<?php

declare(strict_types=1);

namespace Tests\Photos\Domain\Entity;

use MyEspacio\Framework\DataSet;
use MyEspacio\Photos\Domain\Entity\Relevance;
use PHPUnit\Framework\TestCase;

final class RelevanceTest extends TestCase
{
    public function testRelevance(): void
    {
        $relevance = new Relevance(4, 5);

        $this->assertSame(4, $relevance->getCScore());
        $this->assertSame(5, $relevance->getPScore());
        $this->assertEquals(
            [
                'cScore' => 4,
                'pScore' => 5
            ],
            $relevance->jsonSerialize()
        );
    }

    public function testCreateFromDataset(): void
    {
        $data = new DataSet([
            'cscore' => '4',
            'pscore' => '5'
        ]);

        $relevance = Relevance::createFromDataSet($data);
        $this->assertInstanceOf(Relevance::class, $relevance);
        $this->assertSame(4, $relevance->getCScore());
        $this->assertSame(5, $relevance->getPScore());
    }
}
