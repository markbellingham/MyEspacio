<?php

declare(strict_types=1);

namespace Tests\Framework;

use MyEspacio\Framework\Model;

final class TestModel extends Model
{
    public function __set(string $name, mixed $value): void
    {
        $this->$name = $value;
    }
}
