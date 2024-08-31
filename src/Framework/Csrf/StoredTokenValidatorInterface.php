<?php

declare(strict_types=1);

namespace MyEspacio\Framework\Csrf;

interface StoredTokenValidatorInterface
{
    public function validate(string $key, Token $token): bool;
}
