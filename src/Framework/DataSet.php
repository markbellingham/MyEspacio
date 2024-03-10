<?php

declare(strict_types=1);

namespace MyEspacio\Framework;

use DateTimeImmutable;
use Throwable;

final class DataSet
{
    public function __construct(
        private readonly array $data = []
    ) {
    }

    public function string(string $key): string
    {
        $value = $this->data[$key] ?? '';
        if (
            is_scalar($value)
        ) {
            return trim((string)$value);
        } elseif (
            is_array($value) ||
            is_object($value)
        ) {
            return json_encode($value);
        }
        return $value;
    }

    public function int(string $key): int
    {
        if (is_scalar($this->data[$key] ?? '') === false) {
            return 0;
        }
        return filter_var(
            value: $this->data[$key] ?? '',
            filter: FILTER_VALIDATE_INT,
            options: ['options' => ['default' => 0]]
        );
    }

    public function float(string $key): float
    {
        if (is_scalar($this->data[$key] ?? '') === false) {
            return 0.0;
        }
        return filter_var(
            value: $this->data[$key] ?? '',
            filter: FILTER_VALIDATE_FLOAT,
            options: ['options' => ['default' => 0.0]]
        );
    }

    public function bool(string $key): bool
    {
        if (is_scalar($this->data[$key] ?? '') === false) {
            return false;
        }
        return filter_var(
            value: $this->data[$key] ?? '',
            filter: FILTER_VALIDATE_BOOLEAN,
            options: ['options' => ['default' => false]]
        );
    }

    public function dateTimeNull(string $key): ?DateTimeImmutable
    {
        try {
            return new DateTimeImmutable($this->data[$key] ?? null);
        } catch (Throwable) {
            return null;
        }
    }

    public function stringNull(string $key): ?string
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
            return json_encode($value);
        }
        return $value;
    }

    public function intNull(string $key): ?int
    {
        if (is_scalar($this->data[$key] ?? '') === false) {
            return null;
        }
        return filter_var(
            value: $this->data[$key] ?? '',
            filter: FILTER_VALIDATE_INT,
            options: FILTER_NULL_ON_FAILURE
        );
    }

    public function floatNull(string $key): ?float
    {
        if (is_scalar($this->data[$key] ?? '') === false) {
            return null;
        }
        return filter_var(
            value: $this->data[$key] ?? '',
            filter: FILTER_VALIDATE_FLOAT,
            options:FILTER_NULL_ON_FAILURE
        );
    }

    public function boolNull(string $key): ?bool
    {
        if (is_scalar($this->data[$key] ?? '') === false) {
            return null;
        }
        return filter_var(
            value: $this->data[$key] ?? '',
            filter: FILTER_VALIDATE_BOOLEAN,
            options:FILTER_NULL_ON_FAILURE
        );
    }

    public function toArray(): array
    {
        return $this->data;
    }
}
