<?php

declare(strict_types=1);

namespace MyEspacio\Framework;

use DateTimeImmutable;
use InvalidArgumentException;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Throwable;

final readonly class DataSet
{
    /** @param array<string, mixed> $data */
    public function __construct(
        private array $data = []
    ) {
    }

    public function value(string $key): mixed
    {
        return $this->data[$key] ?? null;
    }

    public function string(string $key): string
    {
        return $this->stringNull($key) ?? '';
    }

    public function int(string $key): int
    {
        return $this->intNull($key) ?? 0;
    }

    public function float(string $key): float
    {
        return $this->floatNull($key) ?? 0.00;
    }

    public function bool(string $key): bool
    {
        return $this->boolNull($key) ?? false;
    }

    public function dateTimeNull(string $key): ?DateTimeImmutable
    {
        $value = $this->data[$key] ?? null;
        if (
            $value === null ||
            is_string($value) === false
        ) {
            return null;
        }
        if (str_contains($value, ':') === false) {
            $value .= ' 00:00:00';
        }
        try {
            return new DateTimeImmutable($value);
        } catch (Throwable) {
            return null;
        }
    }

    public function uuidNull(?string $key): ?UuidInterface
    {
        $value = $this->data[$key] ?? null;

        if (is_string($value) === false) {
            return null;
        }

        if (strlen($value) === 16) {
            return Uuid::fromBytes($value);
        }

        if (Uuid::isValid($value)) {
            return Uuid::fromString($value);
        }

        return null;
    }

    public function stringNull(string|null $key): ?string
    {
        $value = $this->data[$key] ?? null;
        if (
            is_scalar($value)
        ) {
            return trim((string)$value);
        } elseif (
            is_array($value) ||
            is_object($value)
        ) {
            $json = json_encode($value);
            if ($json === false) {
                return '[Encoding error]';
            }
            return $json;
        }
        return null;
    }

    public function intNull(string|null $key): ?int
    {
        $value = $this->data[$key] ?? null;
        if (is_scalar($value) === false) {
            return null;
        }
        return filter_var(
            value: $value,
            filter: FILTER_VALIDATE_INT,
            options: FILTER_NULL_ON_FAILURE
        );
    }

    public function floatNull(string|null $key): ?float
    {
        $value = $this->data[$key] ?? null;
        if (is_scalar($value) === false) {
            return null;
        }
        return filter_var(
            value: $value,
            filter: FILTER_VALIDATE_FLOAT,
            options:FILTER_NULL_ON_FAILURE
        );
    }

    public function boolNull(string|null $key): ?bool
    {
        $value = $this->data[$key] ?? null;
        if (is_scalar($value) === false) {
            return null;
        }
        return filter_var(
            value: $value,
            filter: FILTER_VALIDATE_BOOLEAN,
            options:FILTER_NULL_ON_FAILURE
        );
    }

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return $this->data;
    }
}
