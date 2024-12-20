<?php

declare(strict_types=1);

namespace MyEspacio\Photos\Domain\Entity;

use MyEspacio\Common\Domain\Entity\Tag;
use MyEspacio\Framework\DataSet;
use Ramsey\Uuid\UuidInterface;

final class PhotoTag extends Tag
{
    public function __construct(
        private readonly UuidInterface $photoUuid,
        protected string $tag,
        protected ?int $id
    ) {
        parent::__construct(
            tag: $tag,
            id: $id
        );
    }

    public function getPhotoUuid(): UuidInterface
    {
        return $this->photoUuid;
    }

    public function jsonSerialize(): array
    {
        return [
            'photoUuid' => $this->photoUuid->toString(),
            'tag' => $this->tag,
        ];
    }

    public static function createFromDataSet(DataSet $data): PhotoTag
    {
        return new PhotoTag(
            photoUuid: $data->uuidNull('photo_uuid'),
            tag: $data->string('tag'),
            id: $data->int('tag_id')
        );
    }
}
