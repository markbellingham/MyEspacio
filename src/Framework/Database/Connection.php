<?php

declare(strict_types=1);

namespace MyEspacio\Framework\Database;

interface Connection
{
    public function create(string $dbName, array $options = []): PdoConnection;
}
