<?php

namespace MyEspacio\Common\Domain\Entity;

use MyEspacio\Framework\DataSet;
use MyEspacio\Framework\Model;

final class Fave extends Model
{
    public function __construct(
        private int $userId,
        private int $itemId
    ) {
    }

    public function getUserId(): int
    {
        return $this->userId;
    }

    public function setUserId(int $userId): void
    {
        $this->userId = $userId;
    }

    public function getItemId(): int
    {
        return $this->itemId;
    }

    public function setItemId(int $itemId): void
    {
        $this->itemId = $itemId;
    }

    public static function createFromDataSet(DataSet $data): Fave
    {
        return new Fave(
            userId: $data->int('user_id'),
            itemId: $data->int('item_id')
        );
    }
}
