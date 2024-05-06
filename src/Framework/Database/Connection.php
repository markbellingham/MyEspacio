<?php

declare(strict_types=1);

namespace MyEspacio\Framework\Database;

use PDOStatement;

interface Connection
{
    public function fetchOne(string $sql, array $params): ?array;

    public function fetchOneModel(string $sql, array $params, string $fqn);

    public function fetchAll(string $sql, array $params): array;

    public function run(string $sql, array $params): PDOStatement;

    public function statementHasErrors(PDOStatement $stmt): bool;

    public function lastInsertId(): ?int;
}
