<?php

declare(strict_types=1);

namespace MyEspacio\Common\Domain\Entity;

use JsonSerializable;

final class CaptchaIcon implements JsonSerializable
{
    public function __construct(
        private ?int $icon_id = null,
        private ?string $icon = '',
        private ?string $name = '',
        private ?string $colour = ''
    ) {
    }

    public function getIconId(): ?int
    {
        return $this->icon_id;
    }

    public function setIconId(?int $icon_id): void
    {
        $this->icon_id = $icon_id;
    }

    public function getIcon(): ?string
    {
        return $this->icon;
    }

    public function setIcon(?string $icon): void
    {
        $this->icon = $icon;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): void
    {
        $this->name = $name;
    }

    public function getColour(): ?string
    {
        return $this->colour;
    }

    public function setColour(?string $colour): void
    {
        $this->colour = $colour;
    }

    public function jsonSerialize(): array
    {
        return [
            'name' => $this->name
        ];
    }
}
