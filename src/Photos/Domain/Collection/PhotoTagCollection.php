<?php

declare(strict_types=1);

namespace MyEspacio\Photos\Domain\Collection;

use MyEspacio\Framework\ModelCollection;
use MyEspacio\Photos\Domain\Entity\PhotoTag;

/**
 * @template-extends ModelCollection<int, PhotoTag>
 */
final class PhotoTagCollection extends ModelCollection
{
    public function requiredKeys(): array
    {
        return [
            'photo_uuid',
            'tag',
            'id'
        ];
    }

    public function current(): PhotoTag
    {
        $data = $this->currentDataSet();

        return new PhotoTag(
            photoUuid: $data->uuid('photo_uuid'),
            tag: $data->string('tag'),
            id: $data->int('id')
        );
    }
}
