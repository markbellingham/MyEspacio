<?php

declare(strict_types=1);

namespace MyEspacio\Framework;

use JsonSerializable;

class Model implements JsonSerializable
{
    /** @return String[] */
    public function jsonSerialize(): array
    {
        return get_object_vars($this);
    }
}
