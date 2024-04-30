<?php

declare(strict_types=1);

namespace MyEspacio\Framework;

use JsonSerializable;

class Model implements JsonSerializable
{
    public function jsonSerialize(): array
    {
        return get_object_vars($this);
    }
}
