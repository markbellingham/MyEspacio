<?php

declare(strict_types=1);

namespace MyEspacio\Common\Domain\Collection;

use MyEspacio\Common\Domain\Entity\Tag;
use MyEspacio\Framework\ModelCollection;

/**
 * @template-extends ModelCollection<int, Tag>
 */
final class TagCollection extends ModelCollection
{
    public function requiredKeys(): array
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
