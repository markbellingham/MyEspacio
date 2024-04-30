<?php

declare(strict_types=1);

namespace Tests\Framework;

use MyEspacio\Framework\Model;
use PHPUnit\Framework\TestCase;

final class ModelTest extends TestCase
{
    public function testJsonSerializeReturnsPropertiesAsArray()
    {
        $model = new Model();
        $model->property1 = 'value1';
        $model->property2 = 123;

        $serialized = $model->jsonSerialize();

        $expected = [
            'property1' => 'value1',
            'property2' => 123
        ];

        $this->assertEquals($expected, $serialized);
    }

    public function testJsonSerializeReturnsEmptyArrayForEmptyModel()
    {
        $model = new Model();

        $serialized = $model->jsonSerialize();

        $this->assertEquals([], $serialized);
    }
}
