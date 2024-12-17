<?php

declare(strict_types=1);

namespace Tests\Framework\Csrf;

use MyEspacio\Framework\Csrf\Token;
use PHPUnit\Framework\TestCase;

final class TokenTest extends TestCase
{
    public function testTokenToString(): void
    {
        $tokenValue = 'some_token_value';
        $token = new Token($tokenValue);

        $this->assertSame($tokenValue, $token->toString());
    }

    public function testTokenGenerate(): void
    {
        $token = Token::generate();

        $this->assertInstanceOf(Token::class, $token);
        $this->assertNotEmpty($token->toString());
    }

    public function testTokenEquals(): void
    {
        $tokenValue = 'some_token_value';
        $token1 = new Token($tokenValue);
        $token2 = new Token($tokenValue);

        $this->assertTrue($token1->equals($token2));

        $differentTokenValue = 'different_token_value';
        $token3 = new Token($differentTokenValue);

        $this->assertFalse($token1->equals($token3));
    }
}
