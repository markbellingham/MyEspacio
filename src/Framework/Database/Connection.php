<?php

declare(strict_types=1);

namespace MyEspacio\Framework\Database;

use MyEspacio\Framework\Model;
use PDOStatement;

interface Connection
{
    /**
     * @param string $sql
     * @param array<string, mixed> $params
     * @return array<string, mixed>|null
     */
    public function fetchOne(string $sql, array $params): ?array;

    /**
     * @template T of Model
     * @param string $sql
     * @param array<string, mixed> $params
     * @param class-string<T> $fqn
     * @return T|null
     */
    public function fetchOneModel(string $sql, array $params, string $fqn);

    /**
     * @param string $sql
     * @param array<string, mixed> $params
     * @return array<array<string, mixed>>
     */
    public function fetchAll(string $sql, array $params): array;

    /**
     * @param string $sql
     * @param array<string, mixed> $params
     * @return PDOStatement
     */
    public function run(string $sql, array $params): PDOStatement;

    public function statementHasErrors(PDOStatement $stmt): bool;

    public function lastInsertId(): ?int;
}
