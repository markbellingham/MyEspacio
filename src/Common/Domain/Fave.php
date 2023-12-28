<?php

namespace Personly\Common\Domain;

use JsonSerializable;

final class Fave implements JsonSerializable
{
    public function __construct(
        private int $userId,
        private int $itemId
    ) {
    }

    public function jsonSerialize(): array
    {
        return [
            'userId' => $this->userId,
            'itemId' => $this->itemId
        ];
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
}
