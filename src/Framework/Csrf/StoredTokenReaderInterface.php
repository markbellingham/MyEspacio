<?php

declare(strict_types=1);

namespace MyEspacio\Framework\Csrf;

interface StoredTokenReaderInterface
{
    public function read(string $key): Token;
}
