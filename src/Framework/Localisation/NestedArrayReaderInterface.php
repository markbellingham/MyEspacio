<?php

declare(strict_types=1);

namespace MyEspacio\Framework\Localisation;

interface NestedArrayReaderInterface
{
    /** @param array<int, string> $keys */
    public function getValue(array $keys): ?string;

    /** @return array<string, array<string, string>> $data */
    public function getData(): array;

    /** @param array<string, array<string, string>> $data */
    public function setData(array $data): void;

    public function hasData(): bool;
}
