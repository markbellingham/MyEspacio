<?php

declare(strict_types=1);

namespace MyEspacio\Framework\Database;

use PDO;

final class PdoConnectionFactory
{
    /**
     * @param string $dbName
     * @param array<int, mixed> $options
     * @return PdoConnection
     */
    public function create(string $dbName = 'project', array $options = []): PdoConnection
    {
        $dbConfig = DbConfig::fromSettings($dbName);
        $defaultOptions = [
            PDO::ATTR_EMULATE_PREPARES => true,
            PDO:: ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        ];
        $options = array_replace($defaultOptions, $options);
        $dsn = 'mysql:host=' . $dbConfig->host . ';dbname=' . $dbConfig->name . ';charset=' . $dbConfig->charset;
        $pdo = new PDO(
            $dsn,
            $dbConfig->user,
            $dbConfig->password,
            $options
        );
        return new PdoConnection(
            $pdo
        );
    }
}
