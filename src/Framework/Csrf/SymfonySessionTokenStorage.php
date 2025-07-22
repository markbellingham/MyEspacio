<?php

declare(strict_types=1);

namespace MyEspacio\Framework\Csrf;

use Symfony\Component\HttpFoundation\Session\SessionInterface;

final readonly class SymfonySessionTokenStorage implements TokenStorage
{
    public function __construct(
        private SessionInterface $session
    ) {
    }

    public function store(string $key, Token $token): void
    {
        $this->session->set($key, $token->toString());
    }

    public function retrieve(string $key): ?Token
    {
        $tokenValue = $this->session->get($key);
        if (is_string($tokenValue) === false) {
            return null;
        }
        return new Token($tokenValue);
    }
}
