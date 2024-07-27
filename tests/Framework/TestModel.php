<?php

declare(strict_types=1);

namespace Tests\Framework;

use MyEspacio\Framework\Model;

final class TestModel extends Model
{
    public function __set($name, $value)
    {
        $this->$name = $value;
    }
}
