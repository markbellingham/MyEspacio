<?php

declare(strict_types=1);

namespace Tests\Framework;

use MyEspacio\Framework\DataSet;
use PHPUnit\Framework\TestCase;

final class ModelTest extends TestCase
{
    public function testModel(): void
    {
        $dataset = new DataSet([]);

        $testModel = TestModel::createFromDataSet($dataset);
        $this->assertEquals([], $testModel->jsonSerialize());
    }
}
