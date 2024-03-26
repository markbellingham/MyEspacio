<?php

declare(strict_types=1);

namespace MyEspacio\Framework\Localisation;

final class NestedArrayReader
{
    public function __construct(
        private readonly array $data = []
    ) {
    }

    public function getValue($keys)
    {
        if (is_array($keys) === false) {
            $keys = [$keys];
        }

        $value = $this->data;
        foreach ($keys as $key) {
            if (is_array($value) && array_key_exists($key, $value)) {
                $value = $value[$key];
            } else {
                return null;
            }
        }

        return $value;
    }
}
