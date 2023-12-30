<?php

declare(strict_types=1);

namespace MyEspacio\Common\Infrastructure;

use MyEspacio\Common\Domain\CaptchaIconCollection;

interface IconsRepository
{
    public function getIcons(int $qty): CaptchaIconCollection;
}
