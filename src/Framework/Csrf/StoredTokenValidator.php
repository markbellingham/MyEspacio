<?php

declare(strict_types=1);

namespace MyEspacio\Framework\Csrf;

final class StoredTokenValidator implements StoredTokenValidatorInterface
{
    public function __construct(
        private readonly TokenStorage $tokenStorage
    ) {
    }

    public function validate(string $key, Token $token): bool
    {
        $storedToken = $this->tokenStorage->retrieve($key);
        if ($storedToken === null) {
            return false;
        }
        return $token->equals($storedToken);
    }
}
