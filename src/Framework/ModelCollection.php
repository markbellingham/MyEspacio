<?php

declare(strict_types=1);

namespace MyEspacio\Framework;

use Iterator;
use MyEspacio\Framework\Exceptions\CollectionException;

abstract class ModelCollection implements Iterator
{
    protected array $data;
    protected int $position = 0;
    public array $requiredKeys;

    final public function __construct(array $data)
    {
        $this->requiredKeys = static::REQUIRED_KEYS;
        $this->validateElements($data);
        $this->data = $data;
    }

    protected function validateElements(array $data): void
    {
        foreach ($data as $element) {
            if (is_array($element) === false) {
                throw CollectionException::wrongDataType();
            }
            $missingKeys = array_diff($this->requiredKeys, array_keys($element));
            if (!empty($missingKeys)) {
                throw CollectionException::missingRequiredValues($missingKeys);
            }
        }
    }

    abstract public function current(): Model;

    public function rewind(): void
    {
        $this->position = 0;
    }

    public function key(): int
    {
        return $this->position;
    }

    public function next(): void
    {
        $this->position++;
    }

    public function valid(): bool
    {
        return isset($this->data[$this->position]);
    }

    public function currentDataSet(): DataSet
    {
        $element = $this->data[$this->position];
        return new DataSet($element);
    }

    public function toArray(): array
    {
        return $this->data;
    }
}