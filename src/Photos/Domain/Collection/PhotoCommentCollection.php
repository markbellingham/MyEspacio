<?php

declare(strict_types=1);

namespace MyEspacio\Photos\Domain\Collection;

use MyEspacio\Framework\ModelCollection;
use MyEspacio\Photos\Domain\Entity\PhotoComment;

/**
 * @template-extends ModelCollection<int, PhotoComment>
 */
final class PhotoCommentCollection extends ModelCollection
{
    public function requiredKeys(): array
    {
        return [
            'comment',
            'created',
            'photo_uuid',
            'title',
            'user_uuid',
            'username'
        ];
    }

    public function current(): PhotoComment
    {
        $data = $this->currentDataSet();

        return new PhotoComment(
            photoUuid: $data->string('photo_uuid'),
            comment: $data->string('comment'),
            created: $data->dateTimeNull('created'),
            title: $data->string('title'),
            userUuid: $data->uuidNull('user_uuid'),
            username: $data->string('username')
        );
    }
}
