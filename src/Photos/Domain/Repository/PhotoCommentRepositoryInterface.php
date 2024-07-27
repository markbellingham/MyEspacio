<?php

declare(strict_types=1);

namespace MyEspacio\Photos\Domain\Repository;

use MyEspacio\Photos\Domain\Collection\PhotoCommentCollection;
use MyEspacio\Photos\Domain\Entity\PhotoComment;

interface PhotoCommentRepositoryInterface
{
    public function getCommentCount(int $photoId): int;

    public function addComment(PhotoComment $comment);

    public function getPhotoComments(int $photoId): PhotoCommentCollection;
}
