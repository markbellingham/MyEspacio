<?php

declare(strict_types=1);

namespace MyEspacio\Photos\Domain\Entity;

use MyEspacio\Framework\Model;

final class Country extends Model
{
    public function __construct(
        private readonly int $id,
        private readonly string $name,
        private readonly string $twoCharCode,
        private readonly string $threeCharCode
    ) {
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getTwoCharCode(): string
    {
        return $this->twoCharCode;
    }

    public function getThreeCharCode(): string
    {
        return $this->threeCharCode;
    }
}
