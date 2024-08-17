<?php

declare(strict_types=1);

namespace MyEspacio\Common\Domain\Entity;

use MyEspacio\Framework\DataSet;
use MyEspacio\Framework\Model;

class Tag extends Model
{
    public function __construct(
        protected string $tag,
        protected ?int $id = null
    ) {
    }

    public function getTag(): string
    {
        return $this->tag;
    }

    public function setTag(string $tag): void
    {
        $this->tag = $tag;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(?int $id): void
    {
        $this->id = $id;
    }

    public function jsonSerialize(): array
    {
        return [
            'tag' => $this->tag
        ];
    }

    public static function createFromDataSet(DataSet $data): Tag
    {
        return new Tag(
            tag: $data->string('tag'),
            id: $data->intNull('id')
        );
    }
}
