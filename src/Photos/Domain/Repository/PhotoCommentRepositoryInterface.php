<?php

declare(strict_types=1);

namespace MyEspacio\Photos\Domain\Repository;

use MyEspacio\Photos\Domain\Collection\PhotoCommentCollection;
use MyEspacio\Photos\Domain\Entity\Photo;
use MyEspacio\Photos\Domain\Entity\PhotoComment;

interface PhotoCommentRepositoryInterface
{
    public function fetchCount(Photo $photo): int;

    public function save(PhotoComment $comment): bool;

    public function fetchForPhoto(Photo $photo): PhotoCommentCollection;
}
