<?php

declare(strict_types=1);

namespace MyEspacio\Photos\Domain\Entity;

use MyEspacio\Common\Domain\Entity\Tag;
use MyEspacio\Framework\DataSet;

final class PhotoTag extends Tag
{
    public function __construct(
        private readonly string $photoUuid,
        protected string $tag,
        protected ?int $id
    ) {
        parent::__construct(
            tag: $tag,
            id: $id
        );
    }

    public function getPhotoUuid(): string
    {
        return $this->photoUuid;
    }

    public static function createFromDataSet(DataSet $data): PhotoTag
    {
        return new PhotoTag(
            photoUuid: $data->string('photo_uuid'),
            tag: $data->string('tag'),
            id: $data->int('tag_id')
        );
    }
}
