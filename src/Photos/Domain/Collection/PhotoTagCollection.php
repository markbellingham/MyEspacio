<?php

declare(strict_types=1);

namespace MyEspacio\Photos\Domain\Collection;

use MyEspacio\Framework\ModelCollection;
use MyEspacio\Photos\Domain\Entity\PhotoTag;

final class PhotoTagCollection extends ModelCollection
{
    public function getRequiredKeys(): array
    {
        return [
            'photo_id',
            'tag',
            'id'
        ];
    }

    public function current(): PhotoTag
    {
        $data = $this->currentDataSet();

        return new PhotoTag(
            photoId: $data->int('photo_id'),
            tag: $data->string('tag'),
            id: $data->int('id')
        );
    }
}
