<?php

declare(strict_types=1);

namespace Tests\Framework\Csrf;

use MyEspacio\Framework\Csrf\SymfonySessionTokenStorage;
use MyEspacio\Framework\Csrf\Token;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

final class SymfonySessionTokenStorageTest extends TestCase
{
    public function testStoreAndRetrieveToken(): void
    {
        $sessionMock = $this->createMock(SessionInterface::class);

        $key = 'csrf_token_key';
        $tokenValue = 'some_token_value';
        $token = new Token($tokenValue);

        $tokenStorage = new SymfonySessionTokenStorage($sessionMock);

        $sessionMock->expects($this->once())
            ->method('set')
            ->with($key, $tokenValue);

        $tokenStorage->store($key, $token);

        $sessionMock->expects($this->once())
            ->method('get')
            ->with($key)
            ->willReturn($tokenValue);

        $retrievedToken = $tokenStorage->retrieve($key);

        $this->assertInstanceOf(Token::class, $retrievedToken);
        $this->assertSame($tokenValue, $retrievedToken->toString());
    }

    public function testRetrieveNonexistentToken(): void
    {
        $sessionMock = $this->createMock(SessionInterface::class);

        $key = 'nonexistent_key';

        $tokenStorage = new SymfonySessionTokenStorage($sessionMock);

        $sessionMock->expects($this->once())
            ->method('get')
            ->with($key)
            ->willReturn(null);

        $retrievedToken = $tokenStorage->retrieve($key);

        $this->assertNull($retrievedToken);
    }
}
