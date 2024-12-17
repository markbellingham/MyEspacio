<?php

declare(strict_types=1);

namespace MyEspacio\Framework\Csrf;

use Exception;

final readonly class Token
{
    public function __construct(
        private string $token
    ) {
    }

    public function toString(): string
    {
        return $this->token;
    }

    /**
     * @throws Exception
     */
    public static function generate(): Token
    {
        $token = bin2hex(random_bytes(256));
        return new Token($token);
    }

    public function equals(Token $token): bool
    {
        return $this->token === $token->toString();
    }
}
