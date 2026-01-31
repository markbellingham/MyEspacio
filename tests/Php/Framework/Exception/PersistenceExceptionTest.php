<?php

declare(strict_types=1);

namespace Tests\Php\Framework\Exception;

use Exception;
use MyEspacio\Framework\Exceptions\PersistenceException;
use PDOException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class PersistenceExceptionTest extends TestCase
{
    #[DataProvider('persistenceFailedDataProvider')]
    public function testPersistenceFailed(
        Exception $chainedException,
        string $exceptionMessage,
        int $exceptionCode,
    ): void {
        $exception = PersistenceException::persistenceFailed($chainedException);

        $this->assertSame($chainedException, $exception->getPrevious());
        $this->assertSame($exceptionMessage, $exception->getMessage());
        $this->assertSame($exceptionCode, $exception->getCode());
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    public static function persistenceFailedDataProvider(): array
    {
        return [
            'test_1' => [
                'chainedException' => new Exception('Syntax error.', 500),
                'exceptionMessage' => 'Failed to persist data.',
                'exceptionCode' => 500,
            ],
            'test_2' => [
                'chainedException' => new PDOException('Could not connect to the database.', 1001),
                'exceptionMessage' => 'Failed to persist data.',
                'exceptionCode' => 1001,
            ],
        ];
    }
}
