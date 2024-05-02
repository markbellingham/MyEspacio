<?php

declare(strict_types=1);

namespace MyEspacio\Framework\Database;

use InvalidArgumentException;
use MyEspacio\Framework\Model;
use PDO;
use PDOStatement;
use ReflectionClass;
use ReflectionParameter;

class PdoConnection implements Connection
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

    public function fetchOneModel(string $sql, array $params, string $className): ?Model
    {
        if (class_exists($className) === false) {
            throw new InvalidArgumentException("Class '{$className}' does not exist.");
        }

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);

        if ($this->statementHasErrors($stmt)) {
            return null;
        }

        $stmt->setFetchMode(
            PDO::FETCH_CLASS | PDO::FETCH_PROPS_LATE,
            $className,
            $this->getConstructorArguments($className)
        );
        $result = $stmt->fetch();
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

    private function getConstructorArguments(string $className): array
    {
        $reflectionClass = new ReflectionClass($className);
        $constructor = $reflectionClass->getConstructor();

        if (!$constructor) {
            return [];
        }

        $args = [];
        foreach ($constructor->getParameters() as $param) {
            $args[] = $this->getDefaultValueForParameter($param);
        }

        return $args;
    }

    private function getDefaultValueForParameter(ReflectionParameter $param)
    {
        if ($param->isDefaultValueAvailable()) {
            return $param->getDefaultValue();
        }

        if ($param->allowsNull()) {
            return null;
        }

        $typeName = $param->getType()?->getName();
        return match ($typeName) {
            'int', 'float' => 0,
            'string' => '',
            'bool' => false,
            default => null
        };
    }
}
