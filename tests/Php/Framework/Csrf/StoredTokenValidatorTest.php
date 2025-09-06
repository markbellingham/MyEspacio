<?php

declare(strict_types=1);

namespace Tests\Php\Framework\Csrf;

use MyEspacio\Framework\Csrf\StoredTokenValidator;
use MyEspacio\Framework\Csrf\Token;
use MyEspacio\Framework\Csrf\TokenStorage;
use PHPUnit\Framework\TestCase;

final class StoredTokenValidatorTest extends TestCase
{
    public function testValidateMatchingTokens(): void
    {
        $tokenStorageMock = $this->createMock(TokenStorage::class);

        $key = 'csrf_token_key';
        $storedTokenValue = 'some_token_value';
        $tokenValue = 'some_token_value';

        $storedToken = new Token($storedTokenValue);
        $token = new Token($tokenValue);

        $validator = new StoredTokenValidator($tokenStorageMock);

        $tokenStorageMock->expects($this->once())
            ->method('retrieve')
            ->with($key)
            ->willReturn($storedToken);

        $result = $validator->validate($key, $token);

        $this->assertTrue($result);
    }

    public function testValidateMismatchedTokens(): void
    {
        $tokenStorageMock = $this->createMock(TokenStorage::class);

        $key = 'csrf_token_key';
        $storedTokenValue = 'stored_token_value';
        $tokenValue = 'token_value';

        $storedToken = new Token($storedTokenValue);
        $token = new Token($tokenValue);

        $validator = new StoredTokenValidator($tokenStorageMock);

        $tokenStorageMock->expects($this->once())
            ->method('retrieve')
            ->with($key)
            ->willReturn($storedToken);

        $result = $validator->validate($key, $token);

        $this->assertFalse($result);
    }

    public function testValidateNonexistentToken(): void
    {
        $tokenStorageMock = $this->createMock(TokenStorage::class);

        $key = 'nonexistent_key';

        $validator = new StoredTokenValidator($tokenStorageMock);

        $tokenStorageMock->expects($this->once())
            ->method('retrieve')
            ->with($key)
            ->willReturn(null);

        $result = $validator->validate($key, new Token('some_token_value'));

        $this->assertFalse($result);
    }
}
