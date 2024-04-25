<?php

namespace MyEspacio\Common\Domain\Entity;

use JsonSerializable;

final class Fave implements JsonSerializable
{
    public function __construct(
        private int $user_id,
        private int $item_id
    ) {
    }

    public function jsonSerialize(): array
    {
        return get_object_vars($this);
    }

    public function getUserId(): int
    {
        return $this->user_id;
    }

    public function setUserId(int $userId): void
    {
        $this->user_id = $userId;
    }

    public function getItemId(): int
    {
        return $this->item_id;
    }

    public function setItemId(int $itemId): void
    {
        $this->item_id = $itemId;
    }
}
