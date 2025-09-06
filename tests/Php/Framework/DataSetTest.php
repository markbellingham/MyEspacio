<?php

declare(strict_types=1);

namespace Tests\Php\Php\Framework;

use DateMalformedStringException;
use DateTimeImmutable;
use DateTimeZone;
use InvalidArgumentException;
use MyEspacio\Framework\DataSet;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Throwable;

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

    #[DataProvider('utcDateTimeDataProvider')]
    public function testUtcDateTime(
        DataSet $dataset,
        DateTimeImmutable $expectedDateTime
    ): void {
        $this->assertEquals($expectedDateTime, $dataset->utcDateTime('utc'));
    }

    /** @return array<string, array<int, mixed>> */
    public static function utcDateTimeDataProvider(): array
    {
        return [
            'valid_string_1' => [
                new DataSet(['utc' => '2024-03-10 13:35:00']),
                new DateTimeImmutable('2024-03-10 13:35:00', new DateTimeZone('UTC'))
            ],
            'valid_string_2' => [
                new DataSet(['utc' => '2024-06-17T12:34:56+00:00']),
                new DateTimeImmutable('2024-06-17T12:34:56+00:00', new DateTimeZone('UTC'))
            ]
        ];
    }

    /**
     * @param DataSet $dataset
     * @param string $key
     * @param class-string<Throwable> $expectedException
     * @param string $expectedExceptionMessage
     * @return void
     * @throws DateMalformedStringException
     */
    #[DataProvider('utcDateTimeExceptionDataProvider')]
    public function testUtcDateTimeException(
        DataSet $dataset,
        string $key,
        string $expectedException,
        string $expectedExceptionMessage,
    ): void {
        $this->expectException($expectedException);
        $this->expectExceptionMessage($expectedExceptionMessage);

        $dataset->utcDateTime($key);
    }

    /** @return array<int, array<int, mixed>> */
    public static function utcDateTimeExceptionDataProvider(): array
    {
        return [
            [
                new DataSet(['date1' => 123]),
                'date1',
                InvalidArgumentException::class,
                'Invalid date format for key date1'
            ],
            [
                new DataSet(['date2' => []]),
                'date2',
                InvalidArgumentException::class,
                'Invalid date format for key date2'
            ],
            [
                new DataSet(['date3' => 'Invalid date format for key utc']),
                'date3',
                DateMalformedStringException::class,
                'Failed to parse time string (Invalid date format for key utc) at position 0 (I): The timezone could not be found in the database'
            ],
            [
                new DataSet(['date4' => 'bad data']),
                'date4',
                DateMalformedStringException::class,
                'Failed to parse time string (bad data) at position 0 (b): The timezone could not be found in the database'
            ],
        ];
    }

    public function testUtcDateTimeNull(): void
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

        $this->assertInstanceOf(DateTimeImmutable::class, $dataset->utcDateTimeNull('one'));
        $this->assertInstanceOf(DateTimeImmutable::class, $dataset->utcDateTimeNull('two'));
        $this->assertNull($dataset->utcDateTimeNull('three'));
        $this->assertNull($dataset->utcDateTimeNull('four'));
        $this->assertNull($dataset->utcDateTimeNull('five'));
        $this->assertNull($dataset->utcDateTimeNull('six'));
    }

    #[DataProvider('uuidDataProvider')]
    public function testUuid(
        DataSet $dataset,
        UuidInterface $expectedUuid
    ): void {
        $this->assertEquals($expectedUuid, $dataset->uuid('uuid'));
    }

    /** @return array<string, array<int, mixed>> */
    public static function uuidDataProvider(): array
    {
        return [
            'valid_string_1' => [
                new DataSet(['uuid' => '9c001dd7-7921-4944-bc17-52b890aa51fb']),
                Uuid::fromString('9c001dd7-7921-4944-bc17-52b890aa51fb')
            ],
            'valid_string_2' => [
                new DataSet(['uuid' => '550e8400-e29b-41d4-a716-446655440000']),
                Uuid::fromString('550e8400-e29b-41d4-a716-446655440000')
            ],
            'valid_binary_1' => [
                new DataSet(['uuid' => hex2bin('550e8400e29b41d4a716446655440000')]),
                Uuid::fromBytes((string) hex2bin('550e8400e29b41d4a716446655440000'))
            ],
            'valid_binary_2' => [
                new DataSet(['uuid' => hex2bin('123e4567e89b12d3a456426614174000')]),
                Uuid::fromBytes((string) hex2bin('123e4567e89b12d3a456426614174000'))
            ],
        ];
    }

    #[DataProvider('uuidExceptionDataProvider')]
    public function testUuidException(
        DataSet $dataset,
        string $key,
        string $expectedExceptionMessage,
    ): void {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage($expectedExceptionMessage);

        $dataset->uuid($key);
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

    /** @return array<string, array<int, mixed>> */
    public static function uuidExceptionDataProvider(): array
    {
        return [
            'invalid_string_1' => [
                new DataSet(['string1' => '550e8400-e29b-41d4-a716-44665544000']),
                'string1',
                'Invalid UUID format for key string1',
            ],
            'invalid_string_2' => [
                new DataSet(['string2' => '550e8400-e29b-41d4-a716-4466554400000']),
                'string2',
                'Invalid UUID format for key string2'
            ],
            'invalid_string_3' => [
                new DataSet(['string3' => '550e8400-e29b-41d4-a716-zzzzzzzzzzzz']),
                'string3',
                'Invalid UUID format for key string3'
            ],
            'invalid_string_4' => [
                new DataSet(['string4' => 'random-string']),
                'string4',
                'Invalid UUID format for key string4'
            ],
            'invalid_binary_1' => [
                new DataSet(['binary1' => hex2bin('550e8400e29b41d4a71644665544')]),
                'binary1',
                'Invalid UUID format for key binary1'
            ],
            'invalid_binary_2' => [
                new DataSet(['binary2' => hex2bin('550e8400e29b41d4a71644665544000000')]),
                'binary2',
                'Invalid UUID format for key binary2'
            ],
            'invalid_binary_3' => [
                new DataSet(['binary3' => hex2bin('abcd1234abcd1234abcd1234abcd12')]),
                'binary3',
                'Invalid UUID format for key binary3'
            ],
            'invalid_binary_4' => [
                new DataSet(['binary4' => hex2bin('1234567890')]),
                'binary4',
                'Invalid UUID format for key binary4'
            ],
            'null_value' => [
                new DataSet(['null' => null]),
                'null',
                'Invalid UUID format for key null'
            ],
        ];
    }

    public function testStringNullEncodingError(): void
    {
        $recursiveObject = new \stdClass();
        $recursiveObject->self = $recursiveObject;

        $dataset = new DataSet([
            'recursive' => $recursiveObject
        ]);

        $this->assertEquals('[Encoding error]', $dataset->stringNull('recursive'));

        $normalObject = new \stdClass();
        $normalObject->property = 'value';

        $dataset = new DataSet([
            'normal' => $normalObject
        ]);

        $this->assertEquals('{"property":"value"}', $dataset->stringNull('normal'));
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
