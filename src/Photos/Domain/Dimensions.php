<?php

declare(strict_types=1);

namespace MyEspacio\Photos\Domain;

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
}
