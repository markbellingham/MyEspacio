<?php

declare(strict_types=1);

namespace Tests\Php\Framework;

use MyEspacio\Framework\DataSet;
use MyEspacio\Framework\Model;

final class TestModel extends Model
{
    public function __set(string $name, mixed $value): void
    {
        $this->$name = $value;
    }

    public static function createFromDataSet(DataSet $data): TestModel
    {
        $model = new TestModel();
        foreach ($data->toArray() as $key => $value) {
            $model->__set($key, $value);
        }
        return $model;
    }
}
