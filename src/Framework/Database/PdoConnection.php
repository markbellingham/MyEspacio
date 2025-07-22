<?php

declare(strict_types=1);

namespace MyEspacio\Framework\Database;

use InvalidArgumentException;
use MyEspacio\Framework\Model;
use PDO;
use PDOStatement;
use ReflectionClass;
use ReflectionException;
use ReflectionNamedType;
use ReflectionParameter;

final class PdoConnection implements Connection
{
    private const string STATEMENT_SUCCESS_CODE = '00000';

    public function __construct(
        private readonly PDO $pdo
    ) {
    }

    /**
     * @param string $sql
     * @param array<string, mixed> $params
     * @return array<string, mixed>|null
     */
    public function fetchOne(string $sql, array $params): ?array
    {
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        if ($this->statementHasErrors($stmt)) {
            return null;
        }
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($result === false) {
            return null;
        }

        /** @var array<string, mixed> $result */
        return $result;
    }

    /**
     * @template T of Model
     * @param string $sql
     * @param array<string, mixed> $params
     * @param class-string<T> $fqn
     * @return T|null
     */
    public function fetchOneModel(string $sql, array $params, string $fqn)
    {
        if (class_exists($fqn) === false) {
            throw new InvalidArgumentException("Class '{$fqn}' does not exist.");
        }

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);

        if ($this->statementHasErrors($stmt)) {
            return null;
        }

        $stmt->setFetchMode(
            PDO::FETCH_CLASS | PDO::FETCH_PROPS_LATE,
            $fqn,
            $this->getConstructorArguments($fqn)
        );

        /** @var T|null $result */
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

    public function lastInsertId(): int
    {
        return intval($this->pdo->lastInsertId());
    }

    /**
     * @param class-string $className
     * @return array<int, mixed>
     * @throws ReflectionException
     */
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

    private function getDefaultValueForParameter(ReflectionParameter $param): mixed
    {
        if ($param->isDefaultValueAvailable()) {
            return $param->getDefaultValue();
        }

        if ($param->allowsNull()) {
            return null;
        }

        $type = $param->getType();
        if ($type instanceof ReflectionNamedType) {
            return match ($type->getName()) {
                'int', 'float' => 0,
                'string' => '',
                'bool' => false,
                default => null
            };
        }
        return null;
    }
}
