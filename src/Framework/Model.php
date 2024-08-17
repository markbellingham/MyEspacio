<?php

declare(strict_types=1);

namespace MyEspacio\Framework;

use JsonSerializable;

abstract class Model implements JsonSerializable
{
    abstract public static function createFromDataSet(DataSet $data): Model;

    /** @return array<string, mixed> */
    public function jsonSerialize(): array
    {
        return [];
    }
}
