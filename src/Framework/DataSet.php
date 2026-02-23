<?php

declare(strict_types=1);

namespace MyEspacio\Framework;

use DateMalformedStringException;
use DateTimeImmutable;
use DateTimeZone;
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

    public function int(string $key): int
    {
        return $this->intNull($key) ?? 0;
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

    public function float(string $key): float
    {
        return $this->floatNull($key) ?? 0.00;
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

    public function bool(string $key): bool
    {
        return $this->boolNull($key) ?? false;
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

    public function uuid(string $key): UuidInterface
    {
        $uuidNull = $this->uuidNull($key);
        if ($uuidNull === null) {
            throw new InvalidArgumentException('Invalid UUID format for key ' . $key);
        }
        return $uuidNull;
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

    /**
     * @throws DateMalformedStringException
     */
    public function utcDateTime(string $key): DateTimeImmutable
    {
        $value = $this->data[$key] ?? null;
        if (
            $value === null ||
            is_string($value) === false
        ) {
            throw new InvalidArgumentException('Invalid date format for key ' . $key);
        }
        return new DateTimeImmutable($value, new DateTimeZone('UTC'));
    }

    public function utcDateTimeNull(string $key): ?DateTimeImmutable
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
            return new DateTimeImmutable($value, new DateTimeZone('UTC'));
        } catch (Throwable) {
            return null;
        }
    }

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return $this->data;
    }
}
