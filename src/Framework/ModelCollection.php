<?php

declare(strict_types=1);

namespace MyEspacio\Framework;

use Iterator;
use MyEspacio\Framework\Exceptions\CollectionException;

abstract class ModelCollection implements Iterator
{
    /** @var array<int, mixed> */
    protected array $data;
    protected int $position = 0;

    /** @return String[] */
    abstract public function requiredKeys(): array;

    abstract public function current(): Model;

    final public function __construct(array $data)
    {
        $this->validateElements($data);
        $this->data = $data;
    }

    /**
     * @param array<int, mixed> $data
     * @throws CollectionException
     */
    protected function validateElements(array $data): void
    {
        foreach ($data as $element) {
            if (is_array($element) === false) {
                throw CollectionException::wrongDataType();
            }
            $missingKeys = array_diff($this->requiredKeys(), array_keys($element));
            if (!empty($missingKeys)) {
                throw CollectionException::missingRequiredValues($missingKeys);
            }
        }
    }

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

    /** @return array<int, array<string, mixed>> */
    public function toArray(): array
    {
        return $this->data;
    }
}
