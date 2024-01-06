<?php

declare(strict_types=1);

namespace MyEspacio\Framework\Csrf;

use Symfony\Component\HttpFoundation\Session\SessionInterface;

final class SymfonySessionTokenStorage implements TokenStorage
{
    public function __construct(
        private readonly SessionInterface $session
    ) {
    }

    public function store(string $key, Token $token): void
    {
        $this->session->set($key, $token->toString());
    }

    public function retrieve(string $key): ?Token
    {
        $tokenValue = $this->session->get($key);
        if ($tokenValue === null) {
            return null;
        }
        return new Token($tokenValue);
    }
}
