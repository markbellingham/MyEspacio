<?php

declare(strict_types=1);

namespace MyEspacio\Photos\Domain\Collection;

use MyEspacio\Framework\ModelCollection;
use MyEspacio\Photos\Domain\Entity\PhotoComment;

final class PhotoCommentCollection extends ModelCollection
{
    public function getRequiredKeys(): array
    {
        return [
            'comment',
            'created',
            'photo_id',
            'title',
            'user_id',
            'username'
        ];
    }

    public function current(): PhotoComment
    {
        $data = $this->currentDataSet();

        return new PhotoComment(
            photoId: $data->int('photo_id'),
            comment: $data->string('comment'),
            created: $data->dateTimeNull('created'),
            title: $data->string('title'),
            userId: $data->int('user_id'),
            username: $data->string('username')
        );
    }
}
