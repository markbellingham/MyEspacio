<?php

declare(strict_types=1);

namespace MyEspacio\Common\Domain\Collection;

use MyEspacio\Common\Domain\Entity\Tag;
use MyEspacio\Framework\ModelCollection;

final class TagCollection extends ModelCollection
{
    public function getRequiredKeys(): array
    {
        return [
            'tag'
        ];
    }

    public function current(): Tag
    {
        $data = $this->currentDataSet();
        return new Tag(
            tag: $data->string('tag'),
            id: $data->int('id')
        );
    }
}
