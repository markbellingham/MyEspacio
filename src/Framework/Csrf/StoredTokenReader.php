<?php

declare(strict_types=1);

namespace MyEspacio\Framework\Csrf;

use Exception;
use RuntimeException;

final class StoredTokenReader
{
    public function __construct(
        private readonly TokenStorage $tokenStorage
    ) {
    }

    public function read(string $key): Token
    {
        $token = $this->tokenStorage->retrieve($key);
        if ($token !== null) {
            return $token;
        }

        try {
            $token = Token::generate();
            $this->tokenStorage->store($key, $token);
        } catch (Exception $e) {
            throw new RuntimeException('Could not generate token', 0, $e);
        }
        return $token;
    }
}
