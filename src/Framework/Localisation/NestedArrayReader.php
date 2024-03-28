<?php

declare(strict_types=1);

namespace MyEspacio\Framework\Localisation;

final class NestedArrayReader
{
    public function __construct(
        private array $data = []
    ) {
    }

    public function getValue(array $keys): ?string
    {
        $value = $this->data;
        foreach ($keys as $key) {
            if (is_array($value) && array_key_exists($key, $value)) {
                $value = $value[$key];
            }
            if (is_string($value)) {
                break;
            }
        }

        return is_string($value) ? $value : null;
    }

    public function setData(array $data): void
    {
        $this->data = $data;
    }

    public function getData(): array
    {
        return $this->data;
    }

    public function hasData(): bool
    {
        return count($this->data) > 0;
    }
}
