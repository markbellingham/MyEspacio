<?php

declare(strict_types=1);

namespace MyEspacio\Photos\Domain\Entity;

use MyEspacio\Common\Domain\Entity\Tag;
use MyEspacio\Framework\DataSet;

final class PhotoTag extends Tag
{
    public function __construct(
        private readonly int $photoId,
        protected string $tag,
        protected ?int $id
    ) {
        parent::__construct(
            tag: $tag,
            id: $id
        );
    }

    public function getPhotoId(): int
    {
        return $this->photoId;
    }

    public static function createFromDataSet(DataSet $data): PhotoTag
    {
        return new PhotoTag(
            photoId: $data->int('photo_id'),
            tag: $data->string('tag'),
            id: $data->int('tag_id')
        );
    }
}
