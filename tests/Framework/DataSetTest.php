<?php

declare(strict_types=1);

namespace Tests\Framework;

use DateTimeImmutable;
use MyEspacio\Framework\DataSet;
use PHPUnit\Framework\TestCase;

final class DataSetTest extends TestCase
{
    public function testString()
    {
        $dataset = new DataSet([
            'one' => 'one',
            'two' => 'two  ',
            'three' => 3,
            'four' => null,
            'five' => [
                'abc' => 123
            ],
            'six' => true,
            'seven' => false
        ]);
        $this->assertEquals('one', $dataset->string('one'));
        $this->assertEquals('two', $dataset->string('two'));
        $this->assertEquals('3', $dataset->string('three'));
        $this->assertEquals('', $dataset->string('four'));
        $this->assertEquals('{"abc":123}', $dataset->string('five'));
        $this->assertEquals('1', $dataset->string('six'));
        $this->assertEquals('', $dataset->string('seven'));
        $this->assertEquals('', $dataset->string('eight'));
    }

    public function testInt()
    {
        $dataset = new DataSet([
            'one' => '1',
            'two' => 2,
            'three' => 'three',
            'four' => null,
            'five' => [
                'abc' => 123
            ]
        ]);

        $this->assertSame(1, $dataset->int('one'));
        $this->assertSame(2, $dataset->int('two'));
        $this->assertSame(0, $dataset->int('three'));
        $this->assertSame(0, $dataset->int('four'));
        $this->assertSame(0, $dataset->int('five'));
        $this->assertSame(0, $dataset->int('six'));
    }

    public function testFloat()
    {
        $dataset = new DataSet([
            'one' => '1',
            'two' => '2.1',
            'three' => 3.142,
            'four' => null,
            'five' => [
                'abc' => 123
            ]
        ]);

        $this->assertSame(1.0, $dataset->float('one'));
        $this->assertSame(2.1, $dataset->float('two'));
        $this->assertSame(3.142, $dataset->float('three'));
        $this->assertSame(0.0, $dataset->float('four'));
        $this->assertSame(0.0, $dataset->float('five'));
        $this->assertSame(0.0, $dataset->float('six'));
    }

    public function testBool()
    {
        $dataset = new DataSet([
            'one' => 'true',
            'two' => true,
            'three' => '1',
            'four' => 1,
            'five' => 'yes',
            'six' => 'false',
            'seven' => false,
            'eight' => '0',
            'nine' => 0,
            'ten' => 'no',
            'eleven' => null,
            'twelve' => [
                'abc' => 123
            ]
        ]);

        $this->assertTrue($dataset->bool('one'));
        $this->assertTrue($dataset->bool('two'));
        $this->assertTrue($dataset->bool('three'));
        $this->assertTrue($dataset->bool('four'));
        $this->assertTrue($dataset->bool('five'));
        $this->assertFalse($dataset->bool('six'));
        $this->assertFalse($dataset->bool('seven'));
        $this->assertFalse($dataset->bool('eight'));
        $this->assertFalse($dataset->bool('nine'));
        $this->assertFalse($dataset->bool('ten'));
        $this->assertFalse($dataset->bool('eleven'));
        $this->assertFalse($dataset->bool('twelve'));
        $this->assertFalse($dataset->bool('thirteen'));
    }

    public function testDateTimeNull()
    {
        $dataset = new DataSet([
            'one' => '2024-03-10 13:35:00',
            'two' => 'bad data',
            'three' => null,
            'four' => [
                'abc' => 123
            ]
        ]);

        $this->assertInstanceOf(DateTimeImmutable::class, $dataset->dateTimeNull('one'));
        $this->assertNull($dataset->dateTimeNull('two'));
        $this->assertNull($dataset->dateTimeNull('three'));
        $this->assertNull($dataset->dateTimeNull('four'));
        $this->assertNull($dataset->dateTimeNull('five'));
    }

    public function testStringNull()
    {
        $dataset = new DataSet([
            'one' => 'one',
            'two' => 'two   ',
            'three' => 3,
            'four' => null,
            'five' => [
                'abc' => 123
            ],
            'six' => true,
            'seven' => false
        ]);

        $this->assertEquals('one', $dataset->stringNull('one'));
        $this->assertEquals('two', $dataset->stringNull('two'));
        $this->assertEquals('3', $dataset->stringNull('three'));
        $this->assertNull($dataset->stringNull('four'));
        $this->assertEquals('{"abc":123}', $dataset->stringNull('five'));
        $this->assertEquals('1', $dataset->stringNull('six'));
        $this->assertEquals('', $dataset->stringNull('seven'));
        $this->assertNull($dataset->stringNull('eight'));
    }

    public function testIntNull()
    {
        $dataset = new DataSet([
            'one' => '1',
            'two' => 2,
            'three' => 'three',
            'four' => null,
            'five' => [
                'abc' => 123
            ]
        ]);

        $this->assertSame(1, $dataset->intNull('one'));
        $this->assertSame(2, $dataset->intNull('two'));
        $this->assertNull($dataset->intNull('three'));
        $this->assertNull($dataset->intNull('four'));
        $this->assertNull($dataset->intNull('five'));
        $this->assertNull($dataset->intNull('six'));
    }

    public function testFloatNull()
    {
        $dataset = new DataSet([
            'one' => '1',
            'two' => '2.1',
            'three' => 3.142,
            'four' => null,
            'five' => [
                'abc' => 123
            ]
        ]);

        $this->assertSame(1.0, $dataset->floatNull('one'));
        $this->assertSame(2.1, $dataset->floatNull('two'));
        $this->assertSame(3.142, $dataset->floatNull('three'));
        $this->assertNull($dataset->floatNull('four'));
        $this->assertNull($dataset->floatNull('five'));
        $this->assertNull($dataset->floatNull('six'));
    }

    public function testBoolNull()
    {
        $dataset = new DataSet([
            'one' => 'true',
            'two' => true,
            'three' => '1',
            'four' => 1,
            'five' => 'yes',
            'six' => 'false',
            'seven' => false,
            'eight' => '0',
            'nine' => 0,
            'ten' => 'no',
            'eleven' => null,
            'twelve' => [
                'abc' => 123
            ]
        ]);

        $this->assertTrue($dataset->boolNull('one'));
        $this->assertTrue($dataset->boolNull('two'));
        $this->assertTrue($dataset->boolNull('three'));
        $this->assertTrue($dataset->boolNull('four'));
        $this->assertTrue($dataset->boolNull('five'));
        $this->assertFalse($dataset->boolNull('six'));
        $this->assertFalse($dataset->boolNull('seven'));
        $this->assertFalse($dataset->boolNull('eight'));
        $this->assertFalse($dataset->boolNull('nine'));
        $this->assertFalse($dataset->boolNull('ten'));
        $this->assertNull($dataset->boolNull('eleven'));
        $this->assertNull($dataset->boolNull('twelve'));
        $this->assertNull($dataset->boolNull('thirteen'));
    }

    public function testToArray()
    {
        $data = [
            'some' => 'data'
        ];
        $dataset = new DataSet($data);

        $this->assertEquals($data, $dataset->toArray());
    }

    public function testToArrayEmpty()
    {
        $dataset = new DataSet();
        $this->assertEquals([], $dataset->toArray());
    }
}
