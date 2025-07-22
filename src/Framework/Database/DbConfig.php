<?php

declare(strict_types=1);

namespace MyEspacio\Framework\Database;

final readonly class DbConfig
{
    public function __construct(
        public string $host,
        public string $name,
        public string $user,
        public string $password,
        public string $charset,
    ) {
    }

    public static function fromSettings(string $dbName = 'project'): DbConfig
    {
        $config = CONFIG[$dbName] ?? [];
        return new DbConfig(
            $config['db_host'] ?? '',
            $config['db_name'] ?? '',
            $config['db_user'] ?? '',
            $config['db_pass'] ?? '',
            $config['db_char'] ?? 'utf8mb4',
        );
    }
}
