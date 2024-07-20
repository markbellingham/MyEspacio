<?php

declare(strict_types=1);

namespace MyEspacio\Photos\Domain;

use MyEspacio\Common\Domain\Entity\Tag;

final class PhotoTag extends Tag
{
    public function __construct(
        private int $photoId,
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
}
