<?php

declare(strict_types=1);

namespace MyEspacio\Framework;

use Iterator;
use JsonSerializable;
use MyEspacio\Framework\Exceptions\CollectionException;

/**
 * @template TKey of array-key
 * @template TValue of Model
 * @implements Iterator<TKey, TValue>
 */
abstract class ModelCollection implements Iterator, JsonSerializable
{
    /** @var array<int, array<string, mixed>> */
    protected array $data;
    protected int $position = 0;

    /** @return String[] */
    abstract public function requiredKeys(): array;

    abstract public function current(): Model;

    /**
     * @param array<int, array<string, mixed>> $data
     */
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
        foreach ($data as $key => $element) {
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

    /** @return array<int, array<string, mixed>> */
    public function jsonSerialize(): array
    {
        $results = [];
        foreach ($this->data as $index => $element) {
            $this->position = $index;
            $model = $this->current();
            $results[] = $model->jsonSerialize();
        }
        return $results;
    }
}
