<?php

declare(strict_types=1);

namespace Tests\Framework\Csrf;

use MyEspacio\Framework\Csrf\StoredTokenReader;
use MyEspacio\Framework\Csrf\Token;
use MyEspacio\Framework\Csrf\TokenStorage;
use PHPUnit\Framework\TestCase;

class StoredTokenReaderTest extends TestCase
{
    public function testReadExistingToken(): void
    {
        $tokenStorageMock = $this->createMock(TokenStorage::class);

        $key = 'existing_token_key';
        $existingTokenValue = 'existing_token_value';
        $existingToken = new Token($existingTokenValue);

        $tokenStorageMock->expects($this->once())
            ->method('retrieve')
            ->with($key)
            ->willReturn($existingToken);

        $reader = new StoredTokenReader($tokenStorageMock);

        $result = $reader->read($key);

        $this->assertSame($existingTokenValue, $result->toString());
    }

    public function testReadGenerateAndStoreToken(): void
    {
        $tokenStorageMock = $this->createMock(TokenStorage::class);

        $key = 'new_token_key';

        $reader = new StoredTokenReader($tokenStorageMock);

        $tokenStorageMock->expects($this->once())
            ->method('retrieve')
            ->with($key)
            ->willReturn(null);

        $tokenStorageMock->expects($this->once())
            ->method('store');

        $result = $reader->read($key);

        $this->assertInstanceOf(Token::class, $result);
    }

    public function testReadFail(): void
    {
        $tokenStorageMock = $this->createMock(TokenStorage::class);

        $key = 'new_token_key';

        $reader = new StoredTokenReader($tokenStorageMock);

        $tokenStorageMock->expects($this->once())
            ->method('retrieve')
            ->with($key)
            ->willReturn(null);

        $result = $reader->read($key);

        $this->assertInstanceOf(Token::class, $result);
    }
}
