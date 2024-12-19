<?php

declare(strict_types=1);

namespace Tests\Framework;

use DateTimeImmutable;
use MyEspacio\Framework\DataSet;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\UuidInterface;

final class DataSetTest extends TestCase
{
    public function testString(): void
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

    public function testInt(): void
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

    public function testFloat(): void
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

    public function testBool(): void
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

    public function testDateTimeNull(): void
    {
        $dataset = new DataSet([
            'one' => '2024-03-10 13:35:00',
            'two' => '2024-06-17T12:34:56+00:00',
            'three' => 'bad data',
            'four' => null,
            'five' => [
                'abc' => 123
            ]
        ]);

        $this->assertInstanceOf(DateTimeImmutable::class, $dataset->dateTimeNull('one'));
        $this->assertInstanceOf(DateTimeImmutable::class, $dataset->dateTimeNull('two'));
        $this->assertNull($dataset->dateTimeNull('three'));
        $this->assertNull($dataset->dateTimeNull('four'));
        $this->assertNull($dataset->dateTimeNull('five'));
        $this->assertNull($dataset->dateTimeNull('six'));
    }

    public function testUuidNull(): void
    {
        $dataset = new DataSet([
            'valid_string_1' => '9c001dd7-7921-4944-bc17-52b890aa51fb',
            'valid_string_2' => '550e8400-e29b-41d4-a716-446655440000',
            'valid_binary_1' => hex2bin('550e8400e29b41d4a716446655440000'),
            'valid_binary_2' => hex2bin('123e4567e89b12d3a456426614174000'),
            'invalid_string_1' => '550e8400-e29b-41d4-a716-44665544000',
            'invalid_string_2' => '550e8400-e29b-41d4-a716-4466554400000',
            'invalid_string_3' => '550e8400-e29b-41d4-a716-zzzzzzzzzzzz',
            'invalid_binary_1' => hex2bin('550e8400e29b41d4a71644665544'),
            'invalid_binary_2' => hex2bin('550e8400e29b41d4a71644665544000000'),
            'invalid_binary_3' => hex2bin('abcd1234abcd1234abcd1234abcd12'),
            'invalid_binary_4' => hex2bin('1234567890'),
            'invalid_string' => 'random-string',
            'null_value' => null
        ]);

        $this->assertInstanceOf(UuidInterface::class, $dataset->uuidNull('valid_string_1'));
        $this->assertInstanceOf(UuidInterface::class, $dataset->uuidNull('valid_string_2'));
        $this->assertInstanceOf(UuidInterface::class, $dataset->uuidNull('valid_binary_1'));
        $this->assertInstanceOf(UuidInterface::class, $dataset->uuidNull('valid_binary_2'));

        $this->assertNull($dataset->uuidNull('invalid_string_1'));
        $this->assertNull($dataset->uuidNull('invalid_string_2'));
        $this->assertNull($dataset->uuidNull('invalid_string_3'));
        $this->assertNull($dataset->uuidNull('invalid_binary_1'));
        $this->assertNull($dataset->uuidNull('invalid_binary_2'));
        $this->assertNull($dataset->uuidNull('invalid_binary_3'));
        $this->assertNull($dataset->uuidNull('invalid_binary_4'));
        $this->assertNull($dataset->uuidNull('invalid_string'));
        $this->assertNull($dataset->uuidNull('null_value'));
    }

    public function testStringNull(): void
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

    public function testIntNull(): void
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

    public function testFloatNull(): void
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

    public function testBoolNull(): void
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

    public function testToArray(): void
    {
        $data = [
            'some' => 'data'
        ];
        $dataset = new DataSet($data);

        $this->assertEquals($data, $dataset->toArray());
    }

    public function testToArrayEmpty(): void
    {
        $dataset = new DataSet();
        $this->assertEquals([], $dataset->toArray());
    }
}
