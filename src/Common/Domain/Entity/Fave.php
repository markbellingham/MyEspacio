<?php

namespace MyEspacio\Common\Domain\Entity;

use MyEspacio\Framework\DataSet;
use MyEspacio\Framework\Model;
use Ramsey\Uuid\UuidInterface;

class Fave extends Model
{
    public function __construct(
        private UuidInterface $userUuid,
        private UuidInterface $itemUuid
    ) {
    }

    public function getUserUuid(): UuidInterface
    {
        return $this->userUuid;
    }

    public function setUserUuid(UuidInterface $userUuid): void
    {
        $this->userUuid = $userUuid;
    }

    public function getItemUuid(): UuidInterface
    {
        return $this->itemUuid;
    }

    public function setItemUuid(UuidInterface $itemUuid): void
    {
        $this->itemUuid = $itemUuid;
    }

    public static function createFromDataSet(DataSet $data): Fave
    {
        return new Fave(
            userUuid: $data->uuidNull('user_uuid'),
            itemUuid: $data->uuidNull('item_uuid')
        );
    }
}
