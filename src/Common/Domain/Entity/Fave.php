<?php

namespace MyEspacio\Common\Domain\Entity;

use MyEspacio\Framework\DataSet;
use MyEspacio\Framework\Model;

class Fave extends Model
{
    public function __construct(
        private string $userUuid,
        private string $itemUuid
    ) {
    }

    public function getUserUuid(): string
    {
        return $this->userUuid;
    }

    public function setUserUuid(string $userUuid): void
    {
        $this->userUuid = $userUuid;
    }

    public function getItemUuid(): string
    {
        return $this->itemUuid;
    }

    public function setItemUuid(string $itemUuid): void
    {
        $this->itemUuid = $itemUuid;
    }

    public static function createFromDataSet(DataSet $data): Fave
    {
        return new Fave(
            userUuid: $data->string('user_uuid'),
            itemUuid: $data->string('item_uuid')
        );
    }
}
