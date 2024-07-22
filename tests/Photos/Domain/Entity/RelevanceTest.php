<?php

declare(strict_types=1);

namespace Tests\Photos\Domain\Entity;

use PHPUnit\Framework\TestCase;

final class RelevanceTest extends TestCase
{
    public function testRelevance(): void
    {
        $relevance = new \MyEspacio\Photos\Domain\Entity\Relevance(4, 5);

        $this->assertSame(4, $relevance->getCScore());
        $this->assertSame(5, $relevance->getPScore());
    }
}
