<?php

declare(strict_types=1);

namespace MyEspacio\Common\Domain\Repository;

use MyEspacio\Common\Domain\Collection;

interface IconRepositoryInterface
{
    public function getIcons(int $qty): Collection\CaptchaIconCollection;
}
