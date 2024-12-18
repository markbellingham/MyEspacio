<?php

declare(strict_types=1);

namespace MyEspacio\Framework\Database;

use MyEspacio\Framework\Config\Settings;
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
        $dbConfig = Settings::getConfig($dbName);
        $defaultOptions = [
            PDO::ATTR_EMULATE_PREPARES => true,
            PDO:: ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        ];
        $options = array_replace($defaultOptions, $options);
        $dsn = 'mysql:host=' . $dbConfig['db_host'] . ';dbname=' . $dbConfig['db_name'] . ';charset=' . $dbConfig['db_char'];
        $pdo = new PDO(
            $dsn,
            $dbConfig['db_user'],
            $dbConfig['db_pass'],
            $options
        );
        return new PdoConnection(
            $pdo
        );
    }
}
