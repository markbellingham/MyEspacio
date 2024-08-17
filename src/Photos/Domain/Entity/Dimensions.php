<?php

declare(strict_types=1);

namespace MyEspacio\Photos\Domain\Entity;

use MyEspacio\Framework\DataSet;
use MyEspacio\Framework\Model;

final class Dimensions extends Model
{
    public function __construct(
        private readonly int $width,
        private readonly int $height
    ) {
    }

    public function getWidth(): int
    {
        return $this->width;
    }

    public function getHeight(): int
    {
        return $this->height;
    }

    public function jsonSerialize(): array
    {
        return get_object_vars($this);
    }

    public static function createFromDataSet(DataSet $data): Dimensions
    {
        return new Dimensions(
            width: $data->int('width'),
            height: $data->int('height')
        );
    }
}
