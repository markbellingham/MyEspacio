<?php

declare(strict_types=1);

namespace MyEspacio\Framework\Database;

use PDO;
use PDOStatement;

class PdoConnection
{
    private const STATEMENT_SUCCESS_CODE = '00000';

    public function __construct(
        private readonly PDO $pdo
    ) {
    }

    public function fetchOne(string $sql, array $params): ?array
    {
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        if ($this->statementHasErrors($stmt)) {
            return null;
        }
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ?: null;
    }

    public function fetchAll(string $sql, array $params): array
    {
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        if ($this->statementHasErrors($stmt)) {
            return [];
        }
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function run(string $sql, array $params): PDOStatement
    {
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    }

    public function statementHasErrors(PDOStatement $stmt): bool
    {
        $errorInfo = $stmt->errorInfo();
        return $errorInfo[0] !== $this::STATEMENT_SUCCESS_CODE;
    }

    public function lastInsertId(): ?int
    {
        return intval($this->pdo->lastInsertId());
    }
}
