<?php

declare(strict_types=1);

namespace MyEspacio\Common\Domain\Collection;

use MyEspacio\Common\Domain\Entity\CaptchaIcon;
use MyEspacio\Framework\DataSet;
use MyEspacio\Framework\ModelCollection;

/**
 * @template-extends ModelCollection<int, CaptchaIcon>
 */
final class CaptchaIconCollection extends ModelCollection
{
    public function requiredKeys(): array
    {
        return [
            'icon_id',
            'icon',
            'name'
        ];
    }

    public function current(): CaptchaIcon
    {
        $data = $this->currentDataSet();
        return $this->createModel($data);
    }

    public function getRandomIcon(): CaptchaIcon
    {
        $index = array_rand($this->data);
        $selectedIcon = new DataSet($this->data[$index]);
        return $this->createModel($selectedIcon);
    }

    private function createModel(DataSet $data): CaptchaIcon
    {
        return new CaptchaIcon(
            iconId: $data->int('icon_id'),
            icon: $data->string('icon'),
            name:$data->string('name'),
            colour:$data->string('colour')
        );
    }
}
