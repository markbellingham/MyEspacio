<?php

declare(strict_types=1);

namespace MyEspacio\Framework\Localisation;

final class NestedArrayReader
{
    /**
     * @param array<string, array<string, string>> $data
     */
    public function __construct(
        private array $data = []
    ) {
    }

    /**
     * @param array<int, string> $keys
     * @return string|null
     */
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

    /**
     * @param array<string, array<string, string>> $data
     * @return void
     */
    public function setData(array $data): void
    {
        $this->data = $data;
    }

    /**
     * @return array<string, array<string, string>>
     */
    public function getData(): array
    {
        return $this->data;
    }

    public function hasData(): bool
    {
        return count($this->data) > 0;
    }
}
