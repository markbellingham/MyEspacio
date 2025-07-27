<?php

declare(strict_types=1);

namespace MyEspacio\Common\Domain\Entity;

use MyEspacio\Framework\DataSet;
use MyEspacio\Framework\Model;

final class CaptchaIcon extends Model
{
    public function __construct(
        private int $iconId,
        private string $icon,
        private string $name,
        private string $colour
    ) {
    }

    public function getIconId(): int
    {
        return $this->iconId;
    }

    public function setIconId(int $iconId): void
    {
        $this->iconId = $iconId;
    }

    public function getIcon(): string
    {
        return $this->icon;
    }

    public function setIcon(string $icon): void
    {
        $this->icon = $icon;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function getColour(): string
    {
        return $this->colour;
    }

    public function setColour(string $colour): void
    {
        $this->colour = $colour;
    }

    public function jsonSerialize(): array
    {
        return [
            'name' => $this->name
        ];
    }

    public static function createFromDataSet(DataSet $data): CaptchaIcon
    {
        return new CaptchaIcon(
            iconId: $data->int('icon_id'),
            icon: $data->string('icon'),
            name: $data->string('name'),
            colour: $data->string('colour')
        );
    }
}
